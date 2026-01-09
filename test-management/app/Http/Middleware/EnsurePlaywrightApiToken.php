<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlaywrightApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.playwright.api_token', env('PLAYWRIGHT_REPORT_API_TOKEN'));
        if ($expected === '') {
            return response()->json([
                'message' => 'Playwright API token is not configured.',
            ], 500);
        }

        $header = (string) $request->header('Authorization', '');
        $token = '';
        if (preg_match('/^\s*Bearer\s+(.+)\s*$/i', $header, $m) === 1) {
            $token = trim($m[1]);
        }

        if (! hash_equals($expected, $token)) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}

