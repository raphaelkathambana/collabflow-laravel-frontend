<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventHtmlCache
{
    /**
     * Handle an incoming request.
     *
     * Prevents HTML responses from being cached by nginx/FastCGI/browser.
     * This is critical for ensuring asset hash changes are immediately visible.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply to HTML responses
        if (str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            // Additional headers to prevent FastCGI caching
            $response->headers->set('X-Accel-Expires', '0');
        }

        return $response;
    }
}
