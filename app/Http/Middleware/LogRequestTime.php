<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogRequestTime
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        $duration = microtime(true) - $start;

        Log::info('Request Duration: ' . round($duration * 1000, 2) . 'ms', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);

        return $response;
    }
}