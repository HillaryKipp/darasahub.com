<?php

namespace Database\Seeders;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\GradeLearningArea;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\SlugHistory;
use App\Models\Term;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CurriculumTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedGrades();
        $this->seedTerms();
        $this->seedExamTypes();
        $this->seedNationalExams();
        $this->seedOfficialLearningAreas();
    }

    private function seedGrades(): void
    {
        $grades = [
            ['PP1', 'pp1', Grade::LEVEL_PRE_PRIMARY, 1],
            ['PP2', 'pp2', Grade::LEVEL_PRE_PRIMARY, 2],
            ['Grade 1', 'grade-1', Grade::LEVEL_LOWER_PRIMARY, 3],
            ['Grade 2', 'grade-2', Grade::LEVEL_LOWER_PRIMARY, 4],
            ['Grade 3', 'grade-3', Grade::LEVEL_LOWER_PRIMARY, 5],
            ['Grade 4', 'grade-4', Grade::LEVEL_UPPER_PRIMARY, 6],
            ['Grade 5', 'grade-5', Grade::LEVEL_UPPER_PRIMARY, 7],
            ['Grade 6', 'grade-6', Grade::LEVEL_UPPER_PRIMARY, 8],
            ['Grade 7', 'grade-7', Grade::LEVEL_JUNIOR_SCHOOL, 9],
            ['Grade 8', 'grade-8', Grade::LEVEL_JUNIOR_SCHOOL, 10],
            ['Grade 9', 'grade-9', Grade::LEVEL_JUNIOR_SCHOOL, 11],
            ['Grade 10', 'grade-10', Grade::LEVEL_SENIOR_SCHOOL, 12],
            ['Grade 11', 'grade-11', Grade::LEVEL_SENIOR_SCHOOL, 13],
            ['Grade 12', 'grade-12', Grade::LEVEL_SENIOR_SCHOOL, 14],
        ];

        foreach ($grades as [$name, $slug, $level, $order]) {
            Grade::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'level' => $level,
                    'sort_order' => $order,
                    'is_active' => true,
                    'is_demo' => false,
                ]
            );
        }
    }

    private function seedTerms(): void
    {
        $terms = [
            ['Term 1', 'term-1', 1, 1],
            ['Term 2', 'term-2', 2, 2],
            ['Term 3', 'term-3', 3, 3],
        ];

        foreach ($terms as [$name, $slug, $number, $order]) {
            Term::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'number' => $number, 'sort_order' => $order]
            );
        }
    }

    private function seedExamTypes(): void
    {
        $types = [
            ['Opener Exams', 'opener-exams', 'Opener', 1],
            ['Midterm Exams', 'mid-term-exams', 'Midterm', 2],
            ['End Term Exams', 'end-term-exams', 'End Term', 3],
        ];

        foreach ($types as [$name, $slug, $shortLabel, $order]) {
            ExamType::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'short_label' => $shortLabel, 'sort_order' => $order]
            );
        }
    }

    private function seedNationalExams(): void
    {
        $grade6 = Grade::query()->where('slug', 'grade-6')->value('id');
        $grade9 = Grade::query()->where('slug', 'grade-9')->value('id');
        $grade12 = Grade::query()->where('slug', 'grade-12')->value('id');

        $exams = [
            [
                'name' => 'KPSEA',
                'slug' => 'kpsea',
                'full_name' => 'Kenya Primary School Education Assessment',
                'grade_id' => $grade6,
                'curriculum' => NationalExam::CURRICULUM_CBC,
                'verification_status' => NationalExam::STATUS_VERIFIED,
                'sort_order' => 1,
            ],
            [
                'name' => 'KILEA',
                'slug' => 'kilea',
                'full_name' => 'Kenya Intermediate Level Education Assessment',
                'grade_id' => null,
                'curriculum' => NationalExam::CURRICULUM_CBC,
                'verification_status' => NationalExam::STATUS_VERIFIED,
                'sort_order' => 2,
            ],
            [
                'name' => 'KJSEA',
                'slug' => 'kjsea',
                'full_name' => 'Kenya Junior School Education Assessment',
                'grade_id' => $grade9,
                'curriculum' => NationalExam::CURRICULUM_CBC,
                'verification_status' => NationalExam::STATUS_VERIFIED,
                'sort_order' => 3,
            ],
            [
                'name' => 'KCSE',
                'slug' => 'kcse',
                'full_name' => 'Kenya Certificate of Secondary Education',
                'grade_id' => $grade12,
                'curriculum' => NationalExam::CURRICULUM_BOTH,
                'verification_status' => NationalExam::STATUS_VERIFIED,
                'sort_order' => 4,
            ],
        ];

        foreach ($exams as $exam) {
            NationalExam::query()->updateOrCreate(
                ['slug' => $exam['slug']],
                array_merge($exam, ['is_active' => true, 'is_demo' => false])
            );
        }

        // Redirect legacy/alternate shorthand /klea to /kilea
        SlugHistory::query()->firstOrCreate(
            ['old_path' => '/klea'],
            [
                'new_path' => '/kilea',
                'status_code' => 301,
                'model_type' => NationalExam::class,
                'is_active' => true,
            ]
        );
    }

    /**
     * Seeds only the official KICD learning areas provided by the project owner.
     * PP1 & PP2 are intentionally left unmapped until an official Pre-Primary list is supplied.
     */
    private function seedOfficialLearningAreas(): void
    {
        $lowerPrimary = [
            ['English', 'english'],
            ['Kiswahili / Kenya Sign Language (KSL)', 'kiswahili'],
            ['Indigenous Language', 'indigenous-language'],
            ['Mathematics', 'mathematics'],
            ['Environmental Activities', 'environmental-activities'],
            ['Creative Activities', 'creative-activities'],
            ['Religious Education (CRE, IRE, or HRE)', 'religious-education'],
        ];

        $upperPrimary = [
            ['English', 'english'],
            ['Kiswahili / Kenya Sign Language (KSL)', 'kiswahili'],
            ['Mathematics', 'mathematics'],
            ['Science and Technology', 'science-and-technology'],
            ['Agriculture and Nutrition', 'agriculture-and-nutrition'],
            ['Social Studies', 'social-studies'],
            ['Creative Arts', 'creative-arts'],
            ['Religious Education (CRE, IRE, or HRE)', 'religious-education'],
        ];

        $juniorSchool = [
            ['English', 'english'],
            ['Kiswahili / Kenya Sign Language (KSL)', 'kiswahili'],
            ['Mathematics', 'mathematics'],
            ['Integrated Science', 'integrated-science'],
            ['Pre-Technical Studies', 'pre-technical-studies'],
            ['Agriculture and Nutrition', 'agriculture-and-nutrition'],
            ['Social Studies', 'social-studies'],
            ['Creative Arts and Sports', 'creative-arts-and-sports'],
            ['Religious Education (CRE, IRE, or HRE)', 'religious-education'],
        ];

        $seniorSchool = [
            // Core
            ['English', 'english', GradeLearningArea::PATHWAY_CORE],
            ['Kiswahili / Kenya Sign Language (KSL)', 'kiswahili', GradeLearningArea::PATHWAY_CORE],
            ['Core Mathematics', 'core-mathematics', GradeLearningArea::PATHWAY_CORE],
            ['Essential Mathematics', 'essential-mathematics', GradeLearningArea::PATHWAY_CORE],
            ['Community Service Learning (CSL)', 'community-service-learning', GradeLearningArea::PATHWAY_CORE],
            ['Physical Education', 'physical-education', GradeLearningArea::PATHWAY_CORE],
            // STEM Pathway
            ['Biology', 'biology', GradeLearningArea::PATHWAY_STEM],
            ['Chemistry', 'chemistry', GradeLearningArea::PATHWAY_STEM],
            ['Physics', 'physics', GradeLearningArea::PATHWAY_STEM],
            ['General Science', 'general-science', GradeLearningArea::PATHWAY_STEM],
            ['Agriculture', 'agriculture', GradeLearningArea::PATHWAY_STEM],
            ['Computer Science', 'computer-science', GradeLearningArea::PATHWAY_STEM],
            ['Aviation', 'aviation', GradeLearningArea::PATHWAY_STEM],
            ['Building Construction', 'building-construction', GradeLearningArea::PATHWAY_STEM],
            ['Electrical Technology', 'electrical-technology', GradeLearningArea::PATHWAY_STEM],
            ['Metal Technology', 'metal-technology', GradeLearningArea::PATHWAY_STEM],
            ['Power Mechanics', 'power-mechanics', GradeLearningArea::PATHWAY_STEM],
            ['Wood Technology', 'wood-technology', GradeLearningArea::PATHWAY_STEM],
            // Social Sciences Pathway
            ['History and Citizenship', 'history-and-citizenship', GradeLearningArea::PATHWAY_SOCIAL_SCIENCES],
            ['Geography', 'geography', GradeLearningArea::PATHWAY_SOCIAL_SCIENCES],
            ['Business Studies', 'business-studies', GradeLearningArea::PATHWAY_SOCIAL_SCIENCES],
            ['Religious Education (CRE, IRE, or HRE)', 'religious-education', GradeLearningArea::PATHWAY_SOCIAL_SCIENCES],
            ['Languages', 'languages', GradeLearningArea::PATHWAY_SOCIAL_SCIENCES],
            // Arts & Sports Science Pathway
            ['Fine Arts', 'fine-arts', GradeLearningArea::PATHWAY_ARTS_AND_SPORTS],
            ['Music & Dance', 'music-and-dance', GradeLearningArea::PATHWAY_ARTS_AND_SPORTS],
            ['Theatre & Film', 'theatre-and-film', GradeLearningArea::PATHWAY_ARTS_AND_SPORTS],
            ['Sports & Recreation', 'sports-and-recreation', GradeLearningArea::PATHWAY_ARTS_AND_SPORTS],
        ];

        $this->attachAreasToGrades(['grade-1', 'grade-2', 'grade-3'], $lowerPrimary);
        $this->attachAreasToGrades(['grade-4', 'grade-5', 'grade-6'], $upperPrimary);
        $this->attachAreasToGrades(['grade-7', 'grade-8', 'grade-9'], $juniorSchool);
        $this->attachAreasToGrades(['grade-10', 'grade-11', 'grade-12'], $seniorSchool);
    }

    /**
     * @param  array<int, string>  $gradeSlugs
     * @param  array<int, array<int, string>>  $areas
     */
    private function attachAreasToGrades(array $gradeSlugs, array $areas): void
    {
        $grades = Grade::query()->whereIn('slug', $gradeSlugs)->get();

        foreach ($grades as $grade) {
            foreach ($areas as $index => $areaDef) {
                $name = $areaDef[0];
                $slug = $areaDef[1] ?? Str::slug($name);
                $pathway = $areaDef[2] ?? null;

                $area = LearningArea::query()->firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'is_active' => true,
                        'is_demo' => false,
                    ]
                );

                GradeLearningArea::query()->updateOrCreate(
                    [
                        'grade_id' => $grade->id,
                        'learning_area_id' => $area->id,
                    ],
                    [
                        'sort_order' => $index + 1,
                        'pathway' => $pathway,
                        'verification_status' => GradeLearningArea::STATUS_VERIFIED,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
