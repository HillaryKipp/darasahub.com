<?php

namespace App\Services;

use App\Models\LandingPageContent;
use App\Models\SiteSetting;

class IntroSimilarityService
{
    /**
     * Finds near-identical intros across other landing_page_content records (Section 10).
     *
     * @return array<int, array{url_path: string, percent: float}>
     */
    public function findSimilar(string $intro, ?int $ignoreId = null): array
    {
        $clean = strtolower(preg_replace('/\s+/', ' ', trim(strip_tags($intro))) ?: '');
        if (str_word_count($clean) < 20) {
            return [];
        }

        $threshold = (float) SiteSetting::getValue('readiness_similarity_warning_threshold', 75);
        $matches = [];

        LandingPageContent::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->whereNotNull('intro')
            ->select(['id', 'url_path', 'intro'])
            ->chunkById(200, function ($records) use ($clean, $threshold, &$matches): void {
                foreach ($records as $record) {
                    $other = strtolower(preg_replace('/\s+/', ' ', trim(strip_tags((string) $record->intro))) ?: '');
                    if (str_word_count($other) < 20) {
                        continue;
                    }

                    $percent = 0.0;
                    similar_text(substr($clean, 0, 2000), substr($other, 0, 2000), $percent);
                    if ($percent >= $threshold) {
                        $matches[] = [
                            'url_path' => $record->url_path,
                            'percent' => round($percent, 1),
                        ];
                    }
                }
            });

        usort($matches, fn ($a, $b) => $b['percent'] <=> $a['percent']);

        return array_slice($matches, 0, 5);
    }
}
