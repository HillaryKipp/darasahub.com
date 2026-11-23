<?php

namespace Database\Seeders;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LearningArea;
use App\Models\Paper;
use App\Models\Term;
use Illuminate\Database\Seeder;

class GradePastPaperSeeder extends Seeder
{
    /**
     * Seeds all Grade (PP1–Grade 9) past papers and assessments from the Google Drive archive,
     * mapped by Grade, Learning Area, Term (Term 1 / Term 2 / Term 3), and Exam Type.
     */
    public function run(): void
    {
        $records = require __DIR__.'/data/grade_past_papers.php';

        $grades = Grade::query()->pluck('id', 'slug');
        $areas = LearningArea::query()->pluck('id', 'slug');
        $terms = Term::query()->pluck('id', 'slug');
        $examTypes = ExamType::query()->pluck('id', 'slug');
        $now = now();

        foreach ($records as $item) {
            $gradeId = $grades->get($item['grade_slug']);
            $areaId = $areas->get($item['learning_area_slug']);
            $termId = $terms->get($item['term_slug']);
            $examTypeId = $examTypes->get($item['exam_type_slug']);

            if (! $gradeId || ! $areaId || ! $termId || ! $examTypeId) {
                continue;
            }

            Paper::query()->updateOrCreate(
                ['external_file_id' => $item['external_file_id']],
                [
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'category' => Paper::CATEGORY_TERM,
                    'curriculum' => Paper::CURRICULUM_CBC,
                    'grade_id' => $gradeId,
                    'learning_area_id' => $areaId,
                    'term_id' => $termId,
                    'exam_type_id' => $examTypeId,
                    'national_exam_id' => null,
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
        }
    }
}
