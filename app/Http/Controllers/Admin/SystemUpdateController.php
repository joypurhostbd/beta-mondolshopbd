<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SystemUpdateRequest;
use App\Models\SystemDeploymentLog;
use App\Models\SystemUpdateSetting;
use App\Services\SystemUpdateService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemUpdateController extends Controller
{
    public function __construct(
        protected SystemUpdateService $updateService
    ) {
        $this->middleware('auth')->except(['webhookDeploy']);
        $this->middleware('permission:setting-edit')->except(['webhookDeploy']);
    }

    /**
     * Display the System Update & Deployment Manager dashboard.
     */
    public function index()
    {
        $info = $this->updateService->getGitInfo();
        $recentCommits = $this->updateService->getRecentCommits(10);
        $deploymentLogs = $this->updateService->getDeploymentLogs(10);
        $systemMetrics = $this->updateService->getSystemMetrics();
        $remoteStatus = $this->updateService->checkRemoteStatus();

        return view('backEnd.system.update', compact('info', 'recentCommits', 'deploymentLogs', 'systemMetrics', 'remoteStatus'));
    }

    /**
     * Update Git & SSH configuration settings and execution hooks.
     */
    public function update(SystemUpdateRequest $request)
    {
        $settings = SystemUpdateSetting::getSettings();
        $validated = $request->validated();

        $settings->update([
            'protocol' => $validated['protocol'],
            'repository_url' => $validated['repository_url'],
            'branch' => $validated['branch'],
            'https_token' => $validated['https_token'] ?? null,
            'auto_run_composer' => $request->has('auto_run_composer'),
            'auto_run_migrations' => $request->has('auto_run_migrations'),
            'auto_run_optimize' => $request->has('auto_run_optimize'),
            'auto_run_queue_restart' => $request->has('auto_run_queue_restart'),
            'auto_run_npm_build' => $request->has('auto_run_npm_build'),
        ]);

        if ($request->has('auto_generate_key') && empty($settings->ssh_public_key)) {
            $this->updateService->generateEd25519Key();
        }

        $initMsg = '';
        try {
            $env = [];
            if ($settings->protocol === 'ssh' && $settings->ssh_private_key_path && \Illuminate\Support\Facades\File::exists($settings->ssh_private_key_path)) {
                $normalizedKeyPath = str_replace('\\', '/', $settings->ssh_private_key_path);
                $env['GIT_SSH_COMMAND'] = 'ssh -i "' . $normalizedKeyPath . '" -o StrictHostKeyChecking=no';
            }
            $initResult = $this->updateService->ensureGitInitialized(null, $env);
            $initMsg = ' ' . $initResult['message'];
        } catch (\Throwable $e) {
            // Non-blocking during save - will be logged during pipeline execution
        }

        $message = 'System update settings saved successfully!' . $initMsg;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'settings' => $settings->fresh(),
            ]);
        }

        Toastr::success($message, 'Success');

        return redirect()->back()->with('success', $message);
    }

    /**
     * One-click action to initialize or sync Git repository on server.
     */
    public function initGit(Request $request): JsonResponse
    {
        $settings = SystemUpdateSetting::getSettings();
        $env = [];
        if ($settings->protocol === 'ssh' && $settings->ssh_private_key_path && \Illuminate\Support\Facades\File::exists($settings->ssh_private_key_path)) {
            $normalizedKeyPath = str_replace('\\', '/', $settings->ssh_private_key_path);
            $env['GIT_SSH_COMMAND'] = 'ssh -i "' . $normalizedKeyPath . '" -o StrictHostKeyChecking=no';
        }

        try {
            $result = $this->updateService->ensureGitInitialized(null, $env);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate or regenerate an Ed25519 deploy key via AJAX.
     */
    public function generateKey(Request $request): JsonResponse
    {
        $result = $this->updateService->generateEd25519Key();

        return response()->json($result, $result['status'] ? 200 : 500);
    }

    /**
     * Live Server-Sent Events (SSE) stream for the 5-step deployment pipeline.
     */
    public function streamDeployment(Request $request): StreamedResponse
    {
        return response()->stream(function () {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // Connection handshake
            echo "event: connected\n";
            echo "data: " . json_encode(['status' => 'connected', 'message' => 'Pipeline stream initialized.']) . "\n\n";
            flush();

            $this->updateService->executePipeline(function (string $event, int $step, string $message, array $extra = []) {
                echo "event: {$event}\n";
                echo "data: " . json_encode([
                    'event' => $event,
                    'step' => $step,
                    'message' => $message,
                    'extra' => $extra,
                    'time' => date('H:i:s'),
                ]) . "\n\n";

                flush();
            });

            echo "event: stream_end\n";
            echo "data: " . json_encode(['status' => 'closed']) . "\n\n";
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Automated webhook endpoint triggered by CI/CD (GitHub Actions).
     */
    public function webhookDeploy(Request $request): JsonResponse
    {
        $settings = SystemUpdateSetting::getSettings();
        $providedSecret = $request->header('X-Deploy-Secret') ?? $request->query('secret');

        if (empty($settings->webhook_secret) || !hash_equals($settings->webhook_secret, (string) $providedSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing deployment secret.',
            ], 403);
        }

        $logs = [];
        $exitStatus = true;

        $this->updateService->executePipeline(function (string $event, int $step, string $message, array $extra = []) use (&$logs, &$exitStatus) {
            $logs[] = "[{$event}] Step {$step}: {$message}";
            if ($event === 'pipeline_error') {
                $exitStatus = false;
            }
        });

        return response()->json([
            'success' => $exitStatus,
            'message' => $exitStatus ? 'Automated deployment completed successfully.' : 'Deployment pipeline encountered an error.',
            'logs' => $logs,
        ], $exitStatus ? 200 : 500);
    }

    /**
     * Retrieve individual deployment log for terminal modal inspection.
     */
    public function getLog(int $id): JsonResponse
    {
        $log = SystemDeploymentLog::find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Deployment log record not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'log' => $log,
        ]);
    }

    /**
     * Check if remote repository has new commits pending.
     */
    public function checkUpdates(): JsonResponse
    {
        $remoteStatus = $this->updateService->checkRemoteStatus();

        return response()->json([
            'success' => true,
            'remote_status' => $remoteStatus,
        ]);
    }
}
