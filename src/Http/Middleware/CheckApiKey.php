<?php

namespace webdophp\ProSystemsIntegration\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('pro-systems-integration.api_key_data');

        $providedKey = $request->header('API-KEY');

        if ($providedKey !== $expectedKey) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}