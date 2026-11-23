<?php

namespace Database\Seeders;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\Term;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoPaperSeeder extends Seeder
{
    /**
     * Seeds ~50 demo papers flagged is_demo = true.
     * Per Section 4: demo records are ALWAYS draft, unverified, and can NEVER render publicly or enter sitemaps.
     */
    public function run(): void
    {
        if (Paper::query()->where('is_demo', true)->count() >= 50) {
            return;
        }

        $terms = Term::query()->ordered()->get();
        $examTypes = ExamType::query()->ordered()->get();
        $grades = Grade::query()
            ->whereIn('slug', ['grade-4', 'grade-5', 'grade-6', 'grade-7', 'grade-8', 'grade-9'])
            ->with('verifiedLearningAreas')
            ->get();

        $created = 0;

        // 36 demo term-based papers
        foreach ($grades as $grade) {
            $areas = $grade->verifiedLearningAreas->take(3);
            foreach ($areas as $area) {
                foreach ($terms->take(2) as $term) {
                    $examType = $examTypes[$created % $examTypes->count()];
                    $year = 2024 + ($created % 2);
                    $title = sprintf(
                        '[DEMO] %s %s %s %s %d',
                        $grade->name,
                        $area->name,
                        $term->name,
                        $examType->short_label,
                        $year
                    );
                    $slug = Str::slug($title.'-demo-'.($created + 1));

                    Paper::query()->firstOrCreate(
                        ['slug' => $slug],
                        [
                            'title' => $title,
                            'category' => Paper::CATEGORY_TERM,
                            'curriculum' => Paper::CURRICULUM_CBC,
                            'grade_id' => $grade->id,
                            'learning_area_id' => $area->id,
                            'term_id' => $term->id,
                            'exam_type_id' => $examType->id,
                            'year' => $year,
                            'paper_number' => $created % 2 === 0 ? 'Paper 1' : null,
                            'set_label' => 'Demo Set 1',
                            'file_type' => $created % 4 === 3 ? Paper::FILE_TYPE_MARKING_SCHEME : Paper::FILE_TYPE_QUESTION_PAPER,
                            'description' => 'Demo seed record for local development and Filament admin testing. Flagged is_demo = true and excluded from public pages and sitemaps.',
                            'source_name' => 'Demo Seed Source (Not for Publication)',
                            'source_url' => 'https://example.invalid/demo-source',
                            'permission_status' => Paper::PERMISSION_PENDING,
                            'provider' => Paper::PROVIDER_EXTERNAL_LINK,
                            'file_url' => 'https://example.invalid/demo/'.$slug.'.pdf',
                            'file_size' => 450_000 + ($created * 12_000),
                            'availability' => Paper::AVAILABILITY_AVAILABLE,
                            'verification_status' => Paper::VERIFICATION_UNVERIFIED,
                            'publication_status' => Paper::PUB_DRAFT,
                            'is_demo' => true,
                        ]
                    );

                    $created++;
                }
            }
        }

        // 14 demo national exam papers (KPSEA, KJSEA, KCSE)
        $nationalExams = NationalExam::query()->whereIn('slug', ['kpsea', 'kjsea', 'kcse'])->get();
        $nationalAreas = LearningArea::query()->whereIn('slug', ['mathematics', 'english', 'kiswahili', 'integrated-science', 'science-and-technology'])->get();

        foreach ($nationalExams as $exam) {
            foreach ($nationalAreas->take(2) as $area) {
                foreach ([2023, 2024] as $year) {
                    if ($created >= 50) {
                        break 3;
                    }

                    $curriculum = $exam->slug === 'kcse' ? Paper::CURRICULUM_LEGACY_844 : Paper::CURRICULUM_CBC;
                    $title = sprintf('[DEMO] %s %s Past Paper %d', $exam->name, $area->name, $year);
                    $slug = Str::slug($title.'-demo-'.($created + 1));

                    Paper::query()->firstOrCreate(
                        ['slug' => $slug],
                        [
                            'title' => $title,
                            'category' => Paper::CATEGORY_NATIONAL,
                            'curriculum' => $curriculum,
                            'grade_id' => $exam->grade_id,
                            'learning_area_id' => $area->id,
                            'national_exam_id' => $exam->id,
                            'year' => $year,
                            'paper_number' => 'Paper 1',
                            'file_type' => Paper::FILE_TYPE_QUESTION_PAPER,
                            'description' => 'Demo national exam record for internal admin and import testing. Flagged is_demo = true and excluded from public pages and sitemaps.',
                            'source_name' => 'Demo Seed Source (Not for Publication)',
                            'source_url' => 'https://example.invalid/demo-national-source',
                            'permission_status' => Paper::PERMISSION_PENDING,
                            'provider' => Paper::PROVIDER_EXTERNAL_LINK,
                            'file_url' => 'https://example.invalid/demo/'.$slug.'.pdf',
                            'file_size' => 620_000 + ($created * 15_000),
                            'availability' => Paper::AVAILABILITY_AVAILABLE,
                            'verification_status' => Paper::VERIFICATION_UNVERIFIED,
                            'publication_status' => Paper::PUB_DRAFT,
                            'is_demo' => true,
                        ]
                    );

                    $created++;
                }
            }
        }
    }
}
