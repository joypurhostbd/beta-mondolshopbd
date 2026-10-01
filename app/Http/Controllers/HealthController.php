<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Liveness probe endpoint.
     *
     * @return JsonResponse
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Readiness probe endpoint verifying critical dependencies.
     *
     * @return JsonResponse
     */
    public function ready(): JsonResponse
    {
        $checks = [
            'database' => 'ok',
            'cache' => 'ok',
            'storage' => 'ok',
        ];

        $isHealthy = true;

        // 1. Database Check
        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            $checks['database'] = 'error';
            $isHealthy = false;
        }

        // 2. Cache Check
        try {
            Cache::put('health_probe_key', 'ok', 10);
            $cacheVal = Cache::get('health_probe_key');
            if ($cacheVal !== 'ok') {
                $checks['cache'] = 'degraded';
            }
        } catch (Throwable $e) {
            $checks['cache'] = 'error';
            $isHealthy = false;
        }

        // 3. Storage Check
        if (!is_writable(storage_path('framework'))) {
            $checks['storage'] = 'error';
            $isHealthy = false;
        }

        $status = $isHealthy ? 'healthy' : 'unhealthy';
        $statusCode = $isHealthy ? 200 : 503;

        return response()->json([
            'status' => $status,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }
}