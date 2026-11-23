<?php

namespace Database\Seeders;

use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use Illuminate\Database\Seeder;

class KcsePastPaperSeeder extends Seeder
{
    /**
     * Seeds all KCSE (2015–2024) past papers and marking schemes from the Google Drive archive
     * and links marking schemes to their corresponding question papers where matched.
     */
    public function run(): void
    {
        $records = require __DIR__.'/data/kcse_past_papers.php';

        $kcse = NationalExam::query()->where('slug', 'kcse')->first();
        if (! $kcse) {
            return;
        }

        $areas = LearningArea::query()->pluck('id', 'slug');
        $now = now();

        $questionPapersByKey = [];
        $markingSchemes = [];

        foreach ($records as $item) {
            $areaId = $areas->get($item['learning_area_slug']);
            if (! $areaId) {
                continue;
            }

            $paper = Paper::query()->updateOrCreate(
                ['external_file_id' => $item['external_file_id']],
                [
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'category' => Paper::CATEGORY_NATIONAL,
                    'curriculum' => Paper::CURRICULUM_LEGACY_844,
                    'grade_id' => $kcse->grade_id,
                    'learning_area_id' => $areaId,
                    'term_id' => null,
                    'exam_type_id' => null,
                    'national_exam_id' => $kcse->id,
                    'year' => $item['year'],
                    'paper_number' => $item['paper_number'],
                    'set_label' => $item['set_label'],
                    'file_type' => $item['file_type'],
                    'description' => $item['description'],
                    'source_name' => $item['source_name'],
                    'source_url' => $item['source_url'],
                    'permission_status' => Paper::PERMISSION_PERMITTED,
                    'provider' => Paper::PROVIDER_GOOGLE_DRIVE,
                    'file_url' => $item['file_url'],
                    'file_size' => $item['file_size'],
                    'availability' => Paper::AVAILABILITY_AVAILABLE,
                    'verification_status' => Paper::VERIFICATION_VERIFIED,
                    'verified_at' => $now,
                    'publication_status' => Paper::PUB_PUBLISHED,
                    'published_at' => $now,
                    'is_demo' => false,
                ]
            );

            $matchKey = sprintf('%d:%d:%s', $areaId, $item['year'], (string) ($item['paper_number'] ?? ''));

            if ($item['file_type'] === Paper::FILE_TYPE_QUESTION_PAPER && $item['set_label'] !== 'Confidential') {
                $questionPapersByKey[$matchKey] ??= $paper->id;
            } elseif ($item['file_type'] === Paper::FILE_TYPE_MARKING_SCHEME) {
                $markingSchemes[] = [$paper, $matchKey];
            }
        }

        // Link marking schemes to their matching question papers
        foreach ($markingSchemes as [$scheme, $matchKey]) {
            $relatedId = $questionPapersByKey[$matchKey] ?? null;
            if ($relatedId && $scheme->related_paper_id !== $relatedId) {
                $scheme->forceFill(['related_paper_id' => $relatedId])->saveQuietly();
            }
        }
    }
}
