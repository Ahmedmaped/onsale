<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ZapierAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Check for Zapier API key
        $apiKey = $request->header('X-Zapier-API-Key');
        
        if (!$apiKey || $apiKey !== env('ZAPIER_API_KEY')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
} 