<?php

namespace Tests\Feature;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LearningArea;
use App\Models\Paper;
use App\Models\SlugHistory;
use App\Models\Term;
use Database\Seeders\CurriculumTaxonomySeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlugRedirectAndCanonicalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SiteSettingSeeder::class, CurriculumTaxonomySeeder::class]);
    }

    public function test_klea_redirects_301_to_kilea_via_slug_history(): void
    {
        $response = $this->get('/klea');

        $response->assertStatus(301);
        $response->assertRedirect('/kilea');
    }

    public function test_uppercase_path_redirects_301_to_lowercase(): void
    {
        $response = $this->get('/Grade-5');

        $response->assertStatus(301);
        $this->assertStringEndsWith('/grade-5', (string) $response->headers->get('Location'));
    }

    public function test_changing_published_paper_slug_writes_slug_history_and_serves_301(): void
    {
        $grade = Grade::query()->where('slug', 'grade-5')->firstOrFail();
        $area = LearningArea::query()->where('slug', 'kiswahili')->firstOrFail();
        $term = Term::query()->where('slug', 'term-2')->firstOrFail();
        $examType = ExamType::query()->where('slug', 'end-term-exams')->firstOrFail();

        $paper = Paper::factory()->publishedReady()->create([
            'title' => 'Original Paper Title',
            'slug' => 'original-paper-slug',
            'grade_id' => $grade->id,
            'learning_area_id' => $area->id,
            'term_id' => $term->id,
            'exam_type_id' => $examType->id,
            'year' => 2025,
        ]);

        $paper->update(['slug' => 'updated-paper-slug']);

        $this->assertDatabaseHas('slug_history', [
            'old_path' => '/grade-5/kiswahili/term-2/end-term-exams/original-paper-slug',
            'new_path' => '/grade-5/kiswahili/term-2/end-term-exams/updated-paper-slug',
            'status_code' => 301,
        ]);

        $this->get('/grade-5/kiswahili/term-2/end-term-exams/original-paper-slug')
            ->assertStatus(301)
            ->assertRedirect('/grade-5/kiswahili/term-2/end-term-exams/updated-paper-slug');
    }
}
