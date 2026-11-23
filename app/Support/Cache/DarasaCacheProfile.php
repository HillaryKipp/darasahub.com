<?php

namespace App\Support\Cache;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Symfony\Component\HttpFoundation\Response;

class DarasaCacheProfile extends CacheAllSuccessfulGetRequests
{
    public function shouldCacheRequest(Request $request): bool
    {
        if (! parent::shouldCacheRequest($request)) {
            return false;
        }

        // Exclude admin, search, downloads, CSRF forms, livewire, and maintenance routes (Section 12)
        if ($request->is(
            'admin',
            'admin/*',
            'search',
            'search/*',
            'download/*',
            'copyright-takedown',
            'contact',
            'livewire/*',
            '_maintenance/*',
            'up'
        )) {
            return false;
        }

        // Never cache authenticated admin/editor sessions
        if ($request->user() !== null) {
            return false;
        }

        return true;
    }

    public function shouldCacheResponse(Response $response): bool
    {
        if (! parent::shouldCacheResponse($response)) {
            return false;
        }

        // Safety guard: never cache any HTML page containing a CSRF token (Section 12)
        $content = (string) $response->getContent();
        if (str_contains($content, 'name="_token"') || str_contains($content, 'name="csrf-token"')) {
            return false;
        }

        return true;
    }
}
