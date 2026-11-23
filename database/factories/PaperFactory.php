<?php

namespace Database\Factories;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Paper>
 */
class PaperFactory extends Factory
{
    public function definition(): array
    {
        $year = fake()->numberBetween(2023, 2025);
        $title = 'Demo Assessment Paper '.$year.' '.fake()->unique()->bothify('Set-###');

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'category' => Paper::CATEGORY_TERM,
            'curriculum' => Paper::CURRICULUM_CBC,
            'grade_id' => Grade::factory(),
            'learning_area_id' => LearningArea::factory(),
            'term_id' => Term::query()->inRandomOrder()->value('id'),
            'exam_type_id' => ExamType::query()->inRandomOrder()->value('id'),
            'national_exam_id' => null,
            'year' => $year,
            'paper_number' => fake()->randomElement([null, 'Paper 1', 'Paper 2']),
            'set_label' => 'Demo Set '.fake()->numberBetween(1, 3),
            'file_type' => Paper::FILE_TYPE_QUESTION_PAPER,
            'description' => 'Sample demo record created for local development and admin UI testing. Never published publicly.',
            'source_name' => 'Demo Repository (Internal Test)',
            'source_url' => 'https://example.invalid/demo-source',
            'permission_status' => Paper::PERMISSION_PENDING,
            'provider' => Paper::PROVIDER_EXTERNAL_LINK,
            'file_url' => 'https://example.invalid/demo-paper-'.Str::random(8).'.pdf',
            'file_size' => fake()->numberBetween(150_000, 2_500_000),
            'availability' => Paper::AVAILABILITY_AVAILABLE,
            'verification_status' => Paper::VERIFICATION_UNVERIFIED,
            'publication_status' => Paper::PUB_DRAFT,
            'published_at' => null,
            'is_demo' => true,
        ];
    }

    public function national(?NationalExam $exam = null): static
    {
        return $this->state(fn () => [
            'category' => Paper::CATEGORY_NATIONAL,
            'grade_id' => $exam?->grade_id,
            'term_id' => null,
            'exam_type_id' => null,
            'national_exam_id' => $exam?->id ?? NationalExam::query()->inRandomOrder()->value('id'),
        ]);
    }

    public function markingScheme(): static
    {
        return $this->state(fn () => [
            'file_type' => Paper::FILE_TYPE_MARKING_SCHEME,
        ]);
    }

    /**
     * Explicit non-demo verified+published state for automated tests only.
     */
    public function publishedReady(): static
    {
        return $this->state(fn () => [
            'is_demo' => false,
            'permission_status' => Paper::PERMISSION_PERMITTED,
            'verification_status' => Paper::VERIFICATION_VERIFIED,
            'availability' => Paper::AVAILABILITY_AVAILABLE,
            'publication_status' => Paper::PUB_PUBLISHED,
            'published_at' => now(),
            'verified_at' => now(),
            'description' => 'Verified past examination paper with complete metadata, source attribution, and revision context for Kenyan learners preparing for CBC assessments.',
        ]);
    }
}
