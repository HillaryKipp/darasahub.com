<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceCanonicalUrlMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        // Skip admin, livewire, and internal health endpoints
        if ($request->is('admin', 'admin/*', 'livewire/*', 'up', '_maintenance/*')) {
            return $next($request);
        }

        $needsRedirect = false;
        $host = $request->getHost();
        $canonicalHost = config('app.canonical_host', 'darasahub.com');

        // Only enforce canonical host in production (avoid breaking localhost / Herd / tests)
        if (app()->environment('production') && $canonicalHost && strtolower($host) !== strtolower($canonicalHost)) {
            $host = $canonicalHost;
            $needsRedirect = true;
        }

        $rawPath = '/'.ltrim($request->getPathInfo(), '/');
        $normalizedPath = $rawPath;

        // Enforce lowercase paths (skip asset/file-like requests)
        if ($normalizedPath !== strtolower($normalizedPath)) {
            $normalizedPath = strtolower($normalizedPath);
            $needsRedirect = true;
        }

        // Enforce single no-trailing-slash policy (except root '/')
        if ($normalizedPath !== '/' && str_ends_with($normalizedPath, '/')) {
            $normalizedPath = rtrim($normalizedPath, '/');
            $needsRedirect = true;
        }

        if ($needsRedirect) {
            $scheme = app()->environment('production') ? 'https' : $request->getScheme();
            $query = $request->getQueryString();
            $target = $scheme.'://'.$host.$normalizedPath.($query ? '?'.$query : '');

            return redirect()->away($target, 301);
        }

        return $next($request);
    }
}
