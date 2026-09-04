<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = config('app.grabber_api_key');
        
        if (!$apiKey) {
            return $next($request);
        }
        
        $requestKey = $request->header('X-API-Key') ?? $request->query('api_key');
        
        if ($requestKey !== $apiKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return $next($request);
    }
}
