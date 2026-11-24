<?php

namespace Database\Seeders;

use App\Models\SlugHistory;
use Illuminate\Database\Seeder;

class LegacyWordpressRedirectSeeder extends Seeder
{
    /**
     * Seeds permanent (301) redirects from legacy WordPress date-based URLs
     * to their canonical Laravel equivalents to preserve Google rankings.
     */
    public function run(): void
    {
        $redirects = [
            // Top-ranking KCSE WordPress pages (GSC migration map)
            '/2026/01/08/kcse-kiswahili-past-papers' => '/kcse/kiswahili-past-papers',
            '/2026/01/07/kcse-mathematics-past-papers' => '/kcse/mathematics-past-papers',
            '/2026/01/08/kcse-business-studies-past-papers' => '/kcse/business-studies-past-papers',
            '/2026/01/08/kcse-chemistry-past-papers' => '/kcse/chemistry-past-papers',
            '/2025/12/16/kcse-past-papers-all-subjects-years' => '/kcse',
            '/2026/01/08/kcse-history-and-government-past-papers' => '/kcse/history-and-government-past-papers',
            '/2026/01/07/kcse-biology-past-papers' => '/kcse/biology-past-papers',
            '/2026/01/08/kcse-cre-past-papers' => '/kcse/cre-past-papers',
        ];

        foreach ($redirects as $oldPath => $newPath) {
            SlugHistory::query()->updateOrCreate(
                ['old_path' => SlugHistory::normalizePath($oldPath)],
                [
                    'new_path' => SlugHistory::normalizePath($newPath),
                    'status_code' => 301,
                    'model_type' => 'legacy_wordpress',
                    'is_active' => true,
                ]
            );
        }
    }
}
