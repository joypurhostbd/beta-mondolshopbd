<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogContextMiddleware
{
    /**
     * Handle an incoming request and attach structured logging context.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $requestId = (string) Str::uuid();

        // Attach structured request context to all log calls within this lifecycle (Rule 14)
        Log::withContext([
            'request_id' => $requestId,
            'ip' => $request->ip(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_id' => Auth::id() ?? (Auth::guard('customer')->id() ?? null),
        ]);

        $response = $next($request);

        // Attach correlation ID to response headers for traceability
        if (method_exists($response, 'header')) {
            $response->header('X-Request-ID', $requestId);
        }

        return $response;
    }
}