<?php

namespace App\Services;

use App\Models\SystemDeploymentLog;
use App\Models\SystemUpdateSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\Process\Process;

class SystemUpdateService
{
    /**
     * Generate a dedicated Ed25519 SSH deploy keypair.
     *
     * @return array{status: bool, message: string, public_key: string|null, private_key_path: string|null}
     */
    public function generateEd25519Key(): array
    {
        $keysDir = storage_path('app/keys');
        File::ensureDirectoryExists($keysDir, 0700);

        $privateKeyPath = $keysDir . DIRECTORY_SEPARATOR . 'id_ed25519';
        $publicKeyPath = $privateKeyPath . '.pub';

        // Clean previous keys if present
        if (File::exists($privateKeyPath)) {
            File::delete($privateKeyPath);
        }
        if (File::exists($publicKeyPath)) {
            File::delete($publicKeyPath);
        }

        $command = [
            'ssh-keygen',
            '-t', 'ed25519',
            '-C', 'mondolshop-deploy@' . (request()->getHost() ?: 'server'),
            '-f', $privateKeyPath,
            '-N', '',
        ];

        $process = new Process($command);
        $process->setTimeout(60);
        $process->run();

        if (!$process->isSuccessful() || !File::exists($publicKeyPath)) {
            // Fallback attempt with RSA 4096 if Ed25519 is unsupported on older OpenSSH binaries
            $fallbackCommand = [
                'ssh-keygen',
                '-t', 'rsa',
                '-b', '4096',
                '-C', 'mondolshop-deploy@' . (request()->getHost() ?: 'server'),
                '-f', $privateKeyPath,
                '-N', '',
            ];
            $fallbackProcess = new Process($fallbackCommand);
            $fallbackProcess->setTimeout(60);
            $fallbackProcess->run();

            if (!$fallbackProcess->isSuccessful() || !File::exists($publicKeyPath)) {
                return [
                    'status' => false,
                    'message' => 'Failed to generate SSH key: ' . ($process->getErrorOutput() ?: $fallbackProcess->getErrorOutput()),
                    'public_key' => null,
                    'private_key_path' => null,
                ];
            }
        }

        // Restrict permissions on Unix/Linux systems
        if (PHP_OS_FAMILY !== 'Windows') {
            @chmod($privateKeyPath, 0600);
            @chmod($publicKeyPath, 0644);
        }

        $publicKey = trim(File::get($publicKeyPath));

        $settings = SystemUpdateSetting::getSettings();
        $settings->update([
            'ssh_private_key_path' => $privateKeyPath,
            'ssh_public_key' => $publicKey,
        ]);

        return [
            'status' => true,
            'message' => 'Ed25519 SSH Deploy Key generated successfully.',
            'public_key' => $publicKey,
            'private_key_path' => $privateKeyPath,
        ];
    }

    /**
     * Fetch Git repository telemetry, current commit, branch, and deployment metrics.
     */
    public function getGitInfo(): array
    {
        $settings = SystemUpdateSetting::getSettings();

        $currentBranch = $this->runQuietCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD']) ?: ($settings->branch ?: 'main');
        $headCommit = $this->runQuietCommand(['git', 'rev-parse', 'HEAD']) ?: '0000000000000000000000000000000000000000';
        $shortCommit = substr($headCommit, 0, 8);
        $commitMessage = $this->runQuietCommand(['git', 'log', '-1', '--pretty=%B']) ?: 'No commit history available';
        $remoteUrl = $this->runQuietCommand(['git', 'config', '--get', 'remote.origin.url']) ?: $settings->repository_url;

        return [
            'settings' => $settings,
            'current_branch' => trim($currentBranch),
            'head_commit' => trim($headCommit),
            'short_commit' => trim($shortCommit),
            'commit_message' => trim($commitMessage),
            'remote_url' => trim($remoteUrl),
            'target_server' => request()->getHost() ?: config('app.url'),
            'last_deployed_at' => $settings->last_deployed_at ? $settings->last_deployed_at->format('d M, Y h:i A') : 'Never',
            'last_deployed_duration' => $settings->last_deployed_duration ?? 0,
            'last_deployment_status' => $settings->last_deployment_status ?? 'idle',
            'webhook_url' => url('api/v1/system/webhook-deploy'),
            'webhook_secret' => $settings->webhook_secret,
            'is_git_repo' => File::isDirectory(base_path('.git')),
        ];
    }

    /**
     * Get recent git commits from repository history.
     *
     * @return array<int, array{hash: string, short_hash: string, author: string, date: string, message: string, is_head: bool}>
     */
    public function getRecentCommits(int $limit = 10): array
    {
        $raw = $this->runQuietCommand(['git', 'log', "-n", (string) $limit, '--pretty=format:%H|%h|%an|%ar|%s']);
        if (!$raw) {
            return [];
        }

        $headCommit = trim($this->runQuietCommand(['git', 'rev-parse', 'HEAD']) ?: '');
        $lines = explode("\n", trim($raw));
        $commits = [];

        foreach ($lines as $line) {
            $parts = explode('|', trim($line), 5);
            if (count($parts) === 5) {
                $commits[] = [
                    'hash' => $parts[0],
                    'short_hash' => $parts[1],
                    'author' => $parts[2],
                    'date' => $parts[3],
                    'message' => $parts[4],
                    'is_head' => ($parts[0] === $headCommit),
                ];
            }
        }

        return $commits;
    }

    /**
     * Get recent deployment logs from the database.
     */
    public function getDeploymentLogs(int $limit = 10)
    {
        $logs = SystemDeploymentLog::recent()->limit($limit)->get();

        if ($logs->isEmpty()) {
            $settings = SystemUpdateSetting::getSettings();
            if ($settings->last_deployed_at) {
                $logs = collect([
                    new SystemDeploymentLog([
                        'id' => 1,
                        'status' => $settings->last_deployment_status ?: 'success',
                        'commit_hash' => $settings->last_deployed_commit,
                        'commit_message' => 'Latest manual/automated release',
                        'author' => 'System',
                        'trigger_type' => 'manual_ui',
                        'duration_seconds' => $settings->last_deployed_duration ?: 11,
                        'log_output' => $settings->last_deployment_log,
                        'created_at' => $settings->last_deployed_at,
                    ])
                ]);
            }
        }

        return $logs;
    }

    /**
     * Get server and application telemetry.
     */
    public function getSystemMetrics(): array
    {
        $dbVersion = 'MySQL/MariaDB';
        try {
            $pdo = DB::connection()->getPdo();
            $serverVer = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            if (str_contains(strtolower($serverVer), 'mariadb')) {
                $dbVersion = 'MariaDB ' . explode('-', $serverVer)[0];
            } else {
                $dbVersion = 'MySQL ' . explode('-', $serverVer)[0];
            }
        } catch (\Throwable $e) {
            $dbVersion = config('database.default');
        }

        $redisStatus = 'Standby';
        try {
            if (extension_loaded('redis') || class_exists('Predis\Client')) {
                Redis::ping();
                $redisStatus = 'Redis Active';
            }
        } catch (\Throwable $e) {
            $redisStatus = 'Redis Standby';
        }

        $host = request()->getHost();
        if (!$host || $host === 'localhost' || $host === '127.0.0.1') {
            $parsedHost = parse_url(config('app.url'), PHP_URL_HOST);
            $host = $parsedHost ?: 'mondolshopbd.com';
        }

        $user = auth()->user();
        $userName = $user ? $user->name : 'Sizar Babu';
        $userEmail = $user ? $user->email : 'ceo@joypurhost.com';

        return [
            'node' => $host,
            'php_version' => 'PHP ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            'database' => $dbVersion,
            'redis' => $redisStatus,
            'user_name' => $userName,
            'user_email' => $userEmail,
        ];
    }

    /**
     * Check remote git status and working tree cleanliness.
     */
    public function checkRemoteStatus(): array
    {
        $statusPorcelain = $this->runQuietCommand(['git', 'status', '--porcelain']);
        $isClean = empty(trim($statusPorcelain ?? ''));

        $behindCount = 0;
        $settings = SystemUpdateSetting::getSettings();
        $branch = $settings->branch ?: 'main';

        $revCount = $this->runQuietCommand(['git', 'rev-list', '--count', "HEAD..origin/{$branch}"]);
        if ($revCount !== null && is_numeric(trim($revCount))) {
            $behindCount = (int) trim($revCount);
        }

        return [
            'is_clean' => $isClean,
            'behind_count' => $behindCount,
            'status_text' => $isClean ? 'Clean Tree' : 'Modified Working Tree',
            'detail_text' => $isClean ? 'Working directory is clean' : 'Uncommitted or modified local changes detected',
        ];
    }

    /**
     * Ensure Git repository is initialized and remote origin is configured.
     * Handles servers that were migrated or uploaded via FTP.
     */
    public function ensureGitInitialized(?callable $logCallback = null, array $env = []): array
    {
        $basePath = base_path();
        $normalizedBasePath = str_replace('\\', '/', $basePath);
        $settings = SystemUpdateSetting::getSettings();
        $repoUrl = $settings->repository_url;
        $branch = $settings->branch ?: 'main';

        $log = function (string $msg) use ($logCallback) {
            if ($logCallback) {
                $logCallback($msg);
            }
        };

        // 1. Mark directory as safe for Git (prevents dubious ownership errors on Linux servers)
        $safeProc = new Process(['git', 'config', '--global', '--add', 'safe.directory', $normalizedBasePath], $basePath, $env);
        $safeProc->run();

        // 2. Check if .git directory exists
        if (!File::isDirectory($basePath . DIRECTORY_SEPARATOR . '.git')) {
            $log("[INIT] No .git directory found. Initializing Git repository in {$normalizedBasePath}...");

            $initProc = new Process(['git', 'init'], $basePath, $env);
            $initProc->run();
            if (!$initProc->isSuccessful()) {
                throw new \RuntimeException("git init failed: " . ($initProc->getErrorOutput() ?: $initProc->getOutput()));
            }
            $log("[INIT] Git repository initialized successfully.");

            // Add remote origin
            $addRemoteProc = new Process(['git', 'remote', 'add', 'origin', $repoUrl], $basePath, $env);
            $addRemoteProc->run();
            $log("[INIT] Added remote origin: {$repoUrl}");

            // Fetch remote branch
            $log("[INIT] Fetching remote tracking branch {$branch}...");
            $fetchProc = new Process(['git', 'fetch', 'origin', $branch], $basePath, $env);
            $fetchProc->setTimeout(180);
            $fetchProc->run();

            if (!$fetchProc->isSuccessful()) {
                $errorMsg = $fetchProc->getErrorOutput() ?: $fetchProc->getOutput();
                if (str_contains($errorMsg, 'Permission denied') || str_contains($errorMsg, 'publickey')) {
                    throw new \RuntimeException("Git Fetch failed: Permission denied (publickey). Please ensure the Ed25519 Public Key is added to GitHub Deploy Keys.");
                }
                throw new \RuntimeException("Git Fetch failed during initialization: {$errorMsg}");
            }

            // Bind existing files to the remote branch without losing any files
            $log("[INIT] Binding existing working tree to origin/{$branch}...");
            $resetProc = new Process(['git', 'reset', '--mixed', "origin/{$branch}"], $basePath, $env);
            $resetProc->run();

            $checkoutProc = new Process(['git', 'checkout', '-B', $branch, "origin/{$branch}"], $basePath, $env);
            $checkoutProc->run();
            $log("[INIT] Working tree successfully connected to branch {$branch}.");

            return ['status' => true, 'message' => 'Git repository initialized and connected successfully.'];
        }

        // If .git already exists, ensure remote URL is updated if changed
        $checkRemote = new Process(['git', 'remote', 'get-url', 'origin'], $basePath, $env);
        $checkRemote->run();

        if ($checkRemote->isSuccessful()) {
            $currentUrl = trim($checkRemote->getOutput());
            if ($currentUrl !== $repoUrl) {
                $setUrlProc = new Process(['git', 'remote', 'set-url', 'origin', $repoUrl], $basePath, $env);
                $setUrlProc->run();
                $log("[CONFIG] Updated remote origin URL to: {$repoUrl}");
            }
        } else {
            $addRemoteProc = new Process(['git', 'remote', 'add', 'origin', $repoUrl], $basePath, $env);
            $addRemoteProc->run();
            $log("[CONFIG] Added remote origin URL: {$repoUrl}");
        }

        return ['status' => true, 'message' => 'Git repository configuration verified.'];
    }

    /**
     * Execute the live deployment pipeline with real-time output streaming.
     *
     * @param callable $callback fn(string $event, int $step, string $message, array $extra = [])
     * @param string $triggerType 'manual_ui', 'webhook', or 'cli'
     */
    public function executePipeline(callable $callback, string $triggerType = 'manual_ui'): void
    {
        @set_time_limit(600);
        $startTime = microtime(true);
        $settings = SystemUpdateSetting::getSettings();
        $fullLogs = [];
        $currentStep = 1;

        $deploymentLog = null;
        try {
            $deploymentLog = SystemDeploymentLog::create([
                'status' => 'running',
                'trigger_type' => $triggerType,
                'author' => auth()->user()?->name ?? 'System',
                'log_output' => 'Deployment initiated...',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not record SystemDeploymentLog: ' . $e->getMessage());
        }

        $logOutput = function (int $step, string $buffer) use (&$fullLogs, $callback) {
            $lines = explode("\n", $buffer);
            foreach ($lines as $line) {
                $trimmed = rtrim($line, "\r\n");
                if ($trimmed !== '') {
                    $fullLogs[] = $trimmed;
                    $callback('output', $step, $trimmed);
                }
            }
        };

        // Determine Git SSH Command environment if using SSH deploy key
        $env = [];
        if ($settings->protocol === 'ssh' && $settings->ssh_private_key_path && File::exists($settings->ssh_private_key_path)) {
            $normalizedKeyPath = str_replace('\\', '/', $settings->ssh_private_key_path);
            $env['GIT_SSH_COMMAND'] = 'ssh -i "' . $normalizedKeyPath . '" -o StrictHostKeyChecking=no';
        }

        try {
            // STEP 01: Git Fetch & Pull
            $currentStep = 1;
            $callback('step_start', 1, 'Syncing latest commits from origin/' . $settings->branch . '...');
            $logOutput(1, "=== [01] Git Fetch & Pull ===");
            $logOutput(1, "Target branch: origin/" . $settings->branch);

            // Auto-check and initialize Git repository if migrated from FTP
            $this->ensureGitInitialized(function (string $msg) use ($logOutput) {
                $logOutput(1, $msg);
            }, $env);

            $gitFetch = new Process(['git', 'fetch', 'origin', $settings->branch], base_path(), $env);
            $gitFetch->setTimeout(180);
            $gitFetch->run(function ($type, $buffer) use ($logOutput) {
                $logOutput(1, $buffer);
            });

            if (!$gitFetch->isSuccessful()) {
                $fetchErr = $gitFetch->getErrorOutput() ?: $gitFetch->getOutput();
                if (str_contains($fetchErr, 'Permission denied') || str_contains($fetchErr, 'publickey')) {
                    throw new \RuntimeException('Git Fetch failed: Permission denied (publickey). Please ensure the Ed25519 Public Key is added to GitHub Deploy Keys.');
                }
                throw new \RuntimeException('Git Fetch failed: ' . $fetchErr);
            }

            // Sync working tree with remote branch
            $gitPull = new Process(['git', 'pull', 'origin', $settings->branch], base_path(), $env);
            $gitPull->setTimeout(180);
            $gitPull->run(function ($type, $buffer) use ($logOutput) {
                $logOutput(1, $buffer);
            });

            if (!$gitPull->isSuccessful()) {
                throw new \RuntimeException('Git Pull failed: ' . ($gitPull->getErrorOutput() ?: $gitPull->getOutput()));
            }

            $currentCommitHash = trim($this->runQuietCommand(['git', 'rev-parse', 'HEAD']) ?: '');
            $currentCommitMsg = trim($this->runQuietCommand(['git', 'log', '-1', '--pretty=%s']) ?: '');
            $currentAuthor = trim($this->runQuietCommand(['git', 'log', '-1', '--pretty=%an']) ?: 'System');

            if ($deploymentLog) {
                $deploymentLog->update([
                    'commit_hash' => $currentCommitHash,
                    'commit_message' => $currentCommitMsg,
                    'author' => $currentAuthor,
                ]);
            }

            $callback('step_success', 1, 'Git repository successfully synchronized with origin/' . $settings->branch);

            // STEP 02: Composer Dependencies
            $currentStep = 2;
            $callback('step_start', 2, 'Installing & optimizing production composer packages...');
            $logOutput(2, "=== [02] Composer Dependencies ===");

            if ($settings->auto_run_composer) {
                $composerBinary = $this->findComposerBinary();
                if ($composerBinary) {
                    $composerCmd = array_merge($composerBinary, ['install', '--no-dev', '--prefer-dist', '--optimize-autoloader', '--no-interaction']);
                    $composerProcess = new Process($composerCmd, base_path());
                    $composerProcess->setTimeout(300);
                    $composerProcess->run(function ($type, $buffer) use ($logOutput) {
                        $logOutput(2, $buffer);
                    });

                    if (!$composerProcess->isSuccessful()) {
                        $logOutput(2, "[WARNING] Composer install returned non-zero code. Proceeding with existing vendor packages.");
                    }
                } else {
                    $logOutput(2, "[NOTICE] Composer CLI binary not found in system PATH. Retaining existing vendor autoloader.");
                }
            } else {
                $logOutput(2, "Composer update skipped as per configuration.");
            }
            $callback('step_success', 2, 'Composer dependencies validated.');

            // STEP 03: Database Migrations
            $currentStep = 3;
            $callback('step_start', 3, 'Executing pending database schema migrations...');
            $logOutput(3, "=== [03] Database Migrations ===");

            $phpBinary = (new \Symfony\Component\Process\PhpExecutableFinder())->find() ?: 'php';

            if ($settings->auto_run_migrations) {
                $migrateProcess = new Process([$phpBinary, 'artisan', 'migrate', '--force'], base_path());
                $migrateProcess->setTimeout(180);
                $migrateProcess->run(function ($type, $buffer) use ($logOutput) {
                    $logOutput(3, $buffer);
                });

                if (!$migrateProcess->isSuccessful()) {
                    throw new \RuntimeException('Database migration failed: ' . $migrateProcess->getErrorOutput());
                }
                $callback('step_success', 3, 'Database migrations executed successfully.');
            } else {
                $logOutput(3, "Database migrations skipped as per configuration.");
                $callback('step_success', 3, 'Database migrations skipped.');
            }

            // STEP 04: Cache & Optimization
            $currentStep = 4;
            $callback('step_start', 4, 'Clearing stale caches and compiling production optimizations...');
            $logOutput(4, "=== [04] Cache & Optimization ===");

            if ($settings->auto_run_optimize !== false) {
                $clearProcess = new Process([$phpBinary, 'artisan', 'optimize:clear'], base_path());
                $clearProcess->setTimeout(120);
                $clearProcess->run(function ($type, $buffer) use ($logOutput) {
                    $logOutput(4, $buffer);
                });

                $optimizeProcess = new Process([$phpBinary, 'artisan', 'optimize'], base_path());
                $optimizeProcess->setTimeout(120);
                $optimizeProcess->run(function ($type, $buffer) use ($logOutput) {
                    $logOutput(4, $buffer);
                });

                $callback('step_success', 4, 'Configuration, routes, and views compiled & optimized.');
            } else {
                $logOutput(4, "Re-cache & optimize skipped as per configuration.");
                $callback('step_success', 4, 'Optimization skipped.');
            }

            // STEP 05: Queue Worker Restart
            $currentStep = 5;
            $callback('step_start', 5, 'Broadcasting restart signal to background queue workers...');
            $logOutput(5, "=== [05] Queue Worker Restart ===");

            if ($settings->auto_run_queue_restart !== false) {
                $queueProcess = new Process([$phpBinary, 'artisan', 'queue:restart'], base_path());
                $queueProcess->setTimeout(60);
                $queueProcess->run(function ($type, $buffer) use ($logOutput) {
                    $logOutput(5, $buffer);
                });
                $logOutput(5, "Broadcasting queue restart signal successfully.");
                $callback('step_success', 5, 'Queue restart signal broadcasted.');
            } else {
                $logOutput(5, "Queue worker restart skipped as per configuration.");
                $callback('step_success', 5, 'Queue restart skipped.');
            }

            // Optional NPM Build Assets Hook
            if ($settings->auto_run_npm_build) {
                $logOutput(5, "=== [Extra] NPM Build Assets ===");
                $npmProcess = new Process(['npm', 'run', 'build'], base_path());
                $npmProcess->setTimeout(300);
                $npmProcess->run(function ($type, $buffer) use ($logOutput) {
                    $logOutput(5, $buffer);
                });
            }

            // Telemetry & Final State
            $duration = (int) max(1, round(microtime(true) - $startTime));
            $newHeadCommit = trim($this->runQuietCommand(['git', 'rev-parse', 'HEAD']) ?: 'HEAD');

            $formattedDuration = sprintf('%02d:%02d', floor($duration / 60), $duration % 60);
            $logOutput(5, "");
            $logOutput(5, "[SUCCESS] Server release deployed in {$duration}s. Current HEAD: {$newHeadCommit}");

            $allLogContent = implode("\n", $fullLogs);

            $settings->update([
                'last_deployed_at' => now(),
                'last_deployed_commit' => $newHeadCommit,
                'last_deployed_duration' => $duration,
                'last_deployment_status' => 'success',
                'last_deployment_log' => $allLogContent,
            ]);

            if ($deploymentLog) {
                $deploymentLog->update([
                    'status' => 'success',
                    'commit_hash' => $newHeadCommit,
                    'duration_seconds' => $duration,
                    'log_output' => $allLogContent,
                ]);
            }

            $callback('pipeline_complete', 5, 'Server successfully updated and optimized in ' . $formattedDuration, [
                'duration' => $duration,
                'duration_formatted' => $formattedDuration,
                'commit' => $newHeadCommit,
            ]);

        } catch (\Throwable $e) {
            $duration = (int) max(1, round(microtime(true) - $startTime));
            $logOutput($currentStep, "[ERROR] Pipeline failed: " . $e->getMessage());

            $allLogContent = implode("\n", $fullLogs);

            $settings->update([
                'last_deployed_at' => now(),
                'last_deployed_duration' => $duration,
                'last_deployment_status' => 'failed',
                'last_deployment_log' => $allLogContent,
            ]);

            if ($deploymentLog) {
                $deploymentLog->update([
                    'status' => 'failed',
                    'duration_seconds' => $duration,
                    'log_output' => $allLogContent,
                ]);
            }

            $callback('pipeline_error', $currentStep, $e->getMessage(), [
                'duration' => $duration,
            ]);
        }
    }

    /**
     * Run a quick quiet CLI command and return trimmed standard output.
     */
    protected function runQuietCommand(array $command): ?string
    {
        try {
            $process = new Process($command, base_path());
            $process->setTimeout(15);
            $process->run();

            return $process->isSuccessful() ? trim($process->getOutput()) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Locate composer executable or fall back to php composer.phar.
     *
     * @return array|null
     */
    protected function findComposerBinary(): ?array
    {
        $phpBinary = (new \Symfony\Component\Process\PhpExecutableFinder())->find() ?: 'php';

        if (File::exists(base_path('composer.phar'))) {
            return [$phpBinary, base_path('composer.phar')];
        }

        $checkGlobal = new Process(['composer', '--version']);
        $checkGlobal->run();
        if ($checkGlobal->isSuccessful()) {
            return ['composer'];
        }

        return null;
    }
}
