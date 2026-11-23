<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\SitemapService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        $host = (string) SiteSetting::getValue('canonical_host', config('app.canonical_host', 'darasahub.com'));

        // Per Section 12: disallow /admin, /search, and download route; never disallow pages that need noindex read.
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /search',
            'Disallow: /download/',
            'Disallow: /_maintenance/',
            '',
            "Sitemap: https://{$host}/sitemap.xml",
            '',
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(SitemapService $sitemapService): Response
    {
        $path = public_path('sitemap.xml');

        if (! file_exists($path)) {
            $sitemapService->generate();
        }

        return response(file_get_contents($path) ?: '', 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
