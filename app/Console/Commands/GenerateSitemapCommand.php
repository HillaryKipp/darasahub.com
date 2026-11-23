<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = 'Generate chunked XML sitemap index containing only ready public pages.';

    public function handle(SitemapService $sitemapService): int
    {
        $result = $sitemapService->generate();

        $this->info("Generated {$result['index']} with {$result['total_urls']} ready URL(s) across ".count($result['files']).' file(s).');

        return self::SUCCESS;
    }
}
