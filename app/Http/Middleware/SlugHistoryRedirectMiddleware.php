<?php

namespace App\Http\Middleware;

use App\Models\SlugHistory;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SlugHistoryRedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        if ($request->is('admin', 'admin/*', 'livewire/*', 'up', 'sitemap.xml', 'robots.txt', 'sitemaps/*')) {
            return $next($request);
        }

        $path = SlugHistory::normalizePath($request->getPathInfo());

        if ($path === '/') {
            return $next($request);
        }

        try {
            $redirect = SlugHistory::query()
                ->where('old_path', $path)
                ->where('is_active', true)
                ->first();
        } catch (\Throwable) {
            return $next($request);
        }

        if ($redirect && $redirect->new_path !== $path) {
            $redirect->increment('hits');
            $redirect->forceFill(['last_hit_at' => now()])->saveQuietly();

            $query = $request->getQueryString();
            $destination = $redirect->new_path.($query ? '?'.$query : '');

            return redirect($destination, $redirect->status_code ?: 301);
        }

        return $next($request);
    }
}
