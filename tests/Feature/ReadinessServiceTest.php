<?php

namespace Tests\Feature;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LandingPageContent;
use App\Models\LearningArea;
use App\Models\Paper;
use App\Models\Term;
use App\Models\User;
use App\Services\ReadinessService;
use Database\Seeders\CurriculumTaxonomySeeder;
use Database\Seeders\SiteSettingSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadinessServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SiteSettingSeeder::class, CurriculumTaxonomySeeder::class]);
    }

    public function test_demo_paper_cannot_be_published(): void
    {
        $grade = Grade::query()->where('slug', 'grade-5')->firstOrFail();
        $area = LearningArea::query()->where('slug', 'kiswahili')->firstOrFail();
        $term = Term::query()->where('slug', 'term-2')->firstOrFail();
        $examType = ExamType::query()->where('slug', 'end-term-exams')->firstOrFail();

        $this->expectException(DomainException::class);

        Paper::factory()->create([
            'grade_id' => $grade->id,
            'learning_area_id' => $area->id,
            'term_id' => $term->id,
            'exam_type_id' => $examType->id,
            'permission_status' => Paper::PERMISSION_PERMITTED,
            'publication_status' => Paper::PUB_PUBLISHED,
            'is_demo' => true,
        ]);
    }

    public function test_term_landing_requires_verified_paper_and_respects_optional_intro_threshold(): void
    {
        $readiness = app(ReadinessService::class);
        $grade = Grade::query()->where('slug', 'grade-5')->firstOrFail();
        $area = LearningArea::query()->where('slug', 'kiswahili')->firstOrFail();
        $term = Term::query()->where('slug', 'term-2')->firstOrFail();
        $examType = ExamType::query()->where('slug', 'end-term-exams')->firstOrFail();

        // Empty page fails readiness
        $initial = $readiness->evaluateTermLanding($grade, $area, $term, $examType);
        $this->assertFalse($initial->ready);
        $this->assertSame('noindex, follow', $initial->robotsMeta());

        // Add verified published paper -> ready immediately when intro threshold is 0
        Paper::factory()->publishedReady()->create([
            'grade_id' => $grade->id,
            'learning_area_id' => $area->id,
            'term_id' => $term->id,
            'exam_type_id' => $examType->id,
            'year' => 2025,
        ]);

        $readyWithoutIntro = $readiness->evaluateTermLanding($grade, $area, $term, $examType);
        $this->assertTrue($readyWithoutIntro->ready);
        $this->assertSame('index, follow', $readyWithoutIntro->robotsMeta());

        // If admin enables an 80-word intro threshold in SiteSetting, it is enforced
        \App\Models\SiteSetting::setValue('readiness_min_landing_intro_words', 80, 'integer', 'readiness');
        $this->assertFalse($readiness->evaluateTermLanding($grade, $area, $term, $examType)->ready);

        $reviewer = User::factory()->reviewer()->create();
        LandingPageContent::factory()->approvedReady($reviewer)->create([
            'url_path' => '/grade-5/kiswahili/term-2/end-term-exams',
            'page_type' => LandingPageContent::TYPE_TERM_LANDING,
            'intro' => implode(' ', array_fill(0, 90, 'curriculum')),
        ]);

        $this->assertTrue($readiness->evaluateTermLanding($grade, $area, $term, $examType)->ready);
    }

    public function test_ai_assisted_intro_cannot_be_approved_without_human_reviewer(): void
    {
        $this->expectException(DomainException::class);

        LandingPageContent::create([
            'url_path' => '/grade-5/kiswahili/term-2/end-term-exams',
            'page_type' => LandingPageContent::TYPE_TERM_LANDING,
            'intro' => implode(' ', array_fill(0, 95, 'verified')),
            'ai_assisted' => true,
            'reviewed_by' => null,
            'status' => LandingPageContent::STATUS_APPROVED,
            'is_demo' => false,
        ]);
    }
}
