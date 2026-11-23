<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\GradeLearningArea;
use App\Models\LearningArea;
use Illuminate\Database\Seeder;

class DriveLearningAreaSeeder extends Seeder
{
    /**
     * Seeds all learning areas discovered in the Grades (PP1–Grade 9) and KCSE (2015–2024)
     * Google Drive archives, and ensures every Grade -> Learning Area mapping is verified
     * in `grade_learning_area`.
     */
    public function run(): void
    {
        $learningAreas = [
            // Pre-Primary & CBC / Legacy CBC Learning Areas
            'english' => 'English',
            'kiswahili' => 'Kiswahili / Kenya Sign Language (KSL)',
            'mathematics' => 'Mathematics',
            'language-activities' => 'Language Activities',
            'literacy-activities' => 'Literacy Activities',
            'environmental-activities' => 'Environmental Activities',
            'creative-activities' => 'Creative Activities',
            'religious-education' => 'Religious Education (CRE, IRE, or HRE)',
            'art-and-craft-activities' => 'Art and Craft Activities',
            'hygiene-and-nutrition-activities' => 'Hygiene and Nutrition Activities',
            'movement-activities' => 'Movement Activities',
            'science-and-technology' => 'Science and Technology',
            'agriculture-and-nutrition' => 'Agriculture and Nutrition',
            'social-studies' => 'Social Studies',
            'creative-arts' => 'Creative Arts',
            'physical-and-health-education' => 'Physical and Health Education',
            'home-science' => 'Home Science',
            'integrated-science' => 'Integrated Science',
            'pre-technical-studies' => 'Pre-Technical Studies',
            'creative-arts-and-sports' => 'Creative Arts and Sports',
            'health-education' => 'Health Education',
            'life-skills' => 'Life Skills Education',
            'visual-arts' => 'Visual Arts',
            'music' => 'Music',
            'french' => 'French',

            // Senior School & KCSE National Exam Learning Areas
            'agriculture' => 'Agriculture',
            'arabic' => 'Arabic',
            'art-and-design' => 'Art and Design',
            'aviation' => 'Aviation',
            'biology' => 'Biology',
            'building-construction' => 'Building Construction',
            'business-studies' => 'Business Studies',
            'chemistry' => 'Chemistry',
            'computer-studies' => 'Computer Studies',
            'cre' => 'Christian Religious Education (CRE)',
            'drawing-and-design' => 'Drawing and Design',
            'electricity' => 'Electricity',
            'general-science' => 'General Science',
            'geography' => 'Geography',
            'german' => 'German',
            'history-and-government' => 'History and Government',
            'hre' => 'Hindu Religious Education (HRE)',
            'ire' => 'Islamic Religious Education (IRE)',
            'kenyan-sign-language' => 'Kenyan Sign Language (KSL)',
            'metalwork' => 'Metalwork',
            'physics' => 'Physics',
            'power-mechanics' => 'Power Mechanics',
            'woodwork' => 'Woodwork',
            'all-subjects' => 'All Subjects (Compilation)',
        ];

        $areaModels = [];
        foreach ($learningAreas as $slug => $name) {
            $areaModels[$slug] = LearningArea::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'is_active' => true,
                    'is_demo' => false,
                ]
            );
        }

        // Grade -> Learning Areas mappings discovered from the Grades Drive archive (PP1 through Grade 9)
        $gradeMappings = [
            'pp1' => [
                'language-activities',
                'literacy-activities',
                'english',
                'kiswahili',
                'mathematics',
                'environmental-activities',
                'creative-activities',
                'religious-education',
            ],
            'pp2' => [
                'language-activities',
                'literacy-activities',
                'english',
                'kiswahili',
                'mathematics',
                'environmental-activities',
                'creative-activities',
                'religious-education',
            ],
            'grade-1' => [
                'english',
                'environmental-activities',
                'creative-activities',
                'religious-education',
                'language-activities',
                'literacy-activities',
                'art-and-craft-activities',
                'music',
            ],
            'grade-2' => [
                'english',
                'kiswahili',
                'mathematics',
                'environmental-activities',
                'creative-activities',
                'religious-education',
                'language-activities',
                'literacy-activities',
                'hygiene-and-nutrition-activities',
                'movement-activities',
            ],
            'grade-3' => [
                'english',
                'kiswahili',
                'mathematics',
                'environmental-activities',
                'creative-activities',
                'religious-education',
                'language-activities',
                'literacy-activities',
                'art-and-craft-activities',
                'hygiene-and-nutrition-activities',
                'movement-activities',
            ],
            'grade-4' => [
                'english',
                'kiswahili',
                'mathematics',
                'science-and-technology',
                'agriculture-and-nutrition',
                'social-studies',
                'creative-arts',
                'religious-education',
                'agriculture',
                'environmental-activities',
                'home-science',
                'physical-and-health-education',
            ],
            'grade-5' => [
                'english',
                'kiswahili',
                'mathematics',
                'science-and-technology',
                'agriculture-and-nutrition',
                'social-studies',
                'creative-arts',
                'religious-education',
                'agriculture',
                'art-and-craft-activities',
                'home-science',
                'music',
                'physical-and-health-education',
            ],
            'grade-6' => [
                'english',
                'kiswahili',
                'mathematics',
                'science-and-technology',
                'agriculture-and-nutrition',
                'social-studies',
                'creative-arts',
                'religious-education',
                'art-and-craft-activities',
                'home-science',
                'integrated-science',
                'music',
                'physical-and-health-education',
            ],
            'grade-7' => [
                'english',
                'kiswahili',
                'mathematics',
                'integrated-science',
                'pre-technical-studies',
                'agriculture-and-nutrition',
                'social-studies',
                'creative-arts-and-sports',
                'religious-education',
                'agriculture',
                'business-studies',
                'health-education',
                'home-science',
                'life-skills',
                'visual-arts',
            ],
            'grade-8' => [
                'english',
                'kiswahili',
                'mathematics',
                'integrated-science',
                'pre-technical-studies',
                'agriculture-and-nutrition',
                'social-studies',
                'creative-arts-and-sports',
                'religious-education',
                'business-studies',
            ],
            'grade-9' => [
                'english',
                'kiswahili',
                'mathematics',
                'integrated-science',
                'pre-technical-studies',
                'agriculture-and-nutrition',
                'social-studies',
                'creative-arts-and-sports',
                'religious-education',
                'french',
            ],
        ];

        $grades = Grade::query()
            ->whereIn('slug', array_keys($gradeMappings))
            ->get()
            ->keyBy('slug');

        foreach ($gradeMappings as $gradeSlug => $areaSlugs) {
            $grade = $grades->get($gradeSlug);
            if (! $grade) {
                continue;
            }

            $nextOrder = (int) GradeLearningArea::query()
                ->where('grade_id', $grade->id)
                ->max('sort_order');

            foreach ($areaSlugs as $areaSlug) {
                $area = $areaModels[$areaSlug] ?? null;
                if (! $area) {
                    continue;
                }

                $exists = GradeLearningArea::query()
                    ->where('grade_id', $grade->id)
                    ->where('learning_area_id', $area->id)
                    ->exists();

                if (! $exists) {
                    $nextOrder++;
                    GradeLearningArea::query()->create([
                        'grade_id' => $grade->id,
                        'learning_area_id' => $area->id,
                        'sort_order' => $nextOrder,
                        'pathway' => null,
                        'verification_status' => GradeLearningArea::STATUS_VERIFIED,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
