<?php

namespace App\Services;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LandingPageContent;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\SiteSetting;
use App\Models\Term;
use Carbon\Carbon;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    public function __construct(
        private readonly ReadinessService $readiness,
    ) {}

    /**
     * Generates a chunked XML sitemap index (max 5,000 URLs per chunk) split by type.
     * Includes ONLY ready pages (Section 8 & 12).
     *
     * @return array{index: string, files: array<int, string>, total_urls: int}
     */
    public function generate(): array
    {
        $chunkSize = max(100, (int) SiteSetting::getValue('sitemap_chunk_size', 5000));
        $baseUrl = 'https://'.SiteSetting::getValue('canonical_host', config('app.canonical_host', 'darasahub.com'));

        $dir = public_path('sitemaps');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // Remove stale chunks before writing fresh ones
        foreach (glob($dir.DIRECTORY_SEPARATOR.'*.xml') ?: [] as $oldFile) {
            @unlink($oldFile);
        }

        $buckets = [
            'static' => $this->collectStaticUrls($baseUrl),
            'hubs' => $this->collectReadyHubUrls($baseUrl),
            'term-landings' => $this->collectReadyTermLandingUrls($baseUrl),
            'national-landings' => $this->collectReadyNationalLandingUrls($baseUrl),
            'papers' => $this->collectReadyPaperUrls($baseUrl),
        ];

        $index = SitemapIndex::create();
        $writtenFiles = [];
        $totalUrls = 0;

        foreach ($buckets as $type => $entries) {
            if ($entries === []) {
                continue;
            }

            $chunks = array_chunk($entries, $chunkSize);
            foreach ($chunks as $idx => $chunk) {
                $suffix = count($chunks) > 1 ? '-'.($idx + 1) : '';
                $filename = "sitemap-{$type}{$suffix}.xml";
                $filePath = $dir.DIRECTORY_SEPARATOR.$filename;

                $sitemap = Sitemap::create();
                foreach ($chunk as $entry) {
                    $tag = Url::create($entry['loc']);
                    if (! empty($entry['lastmod'])) {
                        $tag->setLastModificationDate($entry['lastmod']);
                    }
                    $sitemap->add($tag);
                }

                $sitemap->writeToFile($filePath);
                $index->add("{$baseUrl}/sitemaps/{$filename}");
                $writtenFiles[] = "/sitemaps/{$filename}";
                $totalUrls += count($chunk);
            }
        }

        $indexPath = public_path('sitemap.xml');
        $index->writeToFile($indexPath);

        return [
            'index' => '/sitemap.xml',
            'files' => $writtenFiles,
            'total_urls' => $totalUrls,
        ];
    }

    /**
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function collectStaticUrls(string $baseUrl): array
    {
        $now = now();
        $paths = ['/', '/latest', '/about', '/contact', '/privacy-policy', '/terms', '/editorial-policy', '/copyright-takedown'];

        return array_map(fn (string $p) => [
            'loc' => $p === '/' ? $baseUrl : $baseUrl.$p,
            'lastmod' => $now,
        ], $paths);
    }

    /**
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function collectReadyHubUrls(string $baseUrl): array
    {
        $urls = [];

        foreach (Grade::query()->active()->ordered()->get() as $grade) {
            if ($this->readiness->evaluateGradeHub($grade)->ready) {
                $urls[] = ['loc' => $baseUrl.'/'.$grade->slug, 'lastmod' => $grade->updated_at ?? now()];
            }

            foreach ($this->readiness->getReadyLearningAreasForGrade($grade) as $area) {
                if ($this->readiness->evaluateLearningAreaHub($grade, $area)->ready) {
                    $urls[] = ['loc' => "{$baseUrl}/{$grade->slug}/{$area->slug}", 'lastmod' => $area->updated_at ?? now()];
                }

                foreach (Term::query()->ordered()->get() as $term) {
                    if ($this->readiness->evaluateTermHub($grade, $area, $term)->ready) {
                        $urls[] = ['loc' => "{$baseUrl}/{$grade->slug}/{$area->slug}/{$term->slug}", 'lastmod' => now()];
                    }
                }
            }
        }

        foreach (NationalExam::query()->active()->ordered()->get() as $exam) {
            if ($this->readiness->evaluateNationalHub($exam)->ready) {
                $urls[] = ['loc' => "{$baseUrl}/{$exam->slug}", 'lastmod' => $exam->updated_at ?? now()];
            }
        }

        return $urls;
    }

    /**
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function collectReadyTermLandingUrls(string $baseUrl): array
    {
        $urls = [];
        $contents = LandingPageContent::query()
            ->approved()
            ->where('page_type', LandingPageContent::TYPE_TERM_LANDING)
            ->get();

        foreach ($contents as $content) {
            if ($this->readiness->evaluate($content)->ready) {
                $urls[] = [
                    'loc' => $baseUrl.$content->url_path,
                    'lastmod' => $content->updated_at ?? now(),
                ];
            }
        }

        return $urls;
    }

    /**
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function collectReadyNationalLandingUrls(string $baseUrl): array
    {
        $urls = [];
        $contents = LandingPageContent::query()
            ->approved()
            ->whereIn('page_type', [
                LandingPageContent::TYPE_NATIONAL_SUBJECT,
                LandingPageContent::TYPE_NATIONAL_YEAR,
            ])
            ->get();

        foreach ($contents as $content) {
            if ($this->readiness->evaluate($content)->ready) {
                $urls[] = [
                    'loc' => $baseUrl.$content->url_path,
                    'lastmod' => $content->updated_at ?? now(),
                ];
            }
        }

        return $urls;
    }

    /**
     * @return array<int, array{loc: string, lastmod: Carbon}>
     */
    private function collectReadyPaperUrls(string $baseUrl): array
    {
        $urls = [];

        Paper::query()
            ->verifiedReady()
            ->with(['grade', 'learningArea', 'term', 'examType', 'nationalExam'])
            ->chunkById(500, function ($papers) use (&$urls, $baseUrl): void {
                foreach ($papers as $paper) {
                    if ($this->readiness->evaluatePaper($paper)->ready && $paper->url_path) {
                        $urls[] = [
                            'loc' => $baseUrl.$paper->url_path,
                            'lastmod' => $paper->updated_at ?? now(),
                        ];
                    }
                }
            });

        return $urls;
    }
}
