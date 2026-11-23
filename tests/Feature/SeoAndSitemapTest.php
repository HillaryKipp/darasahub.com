<?php

namespace Tests\Feature;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LandingPageContent;
use App\Models\LearningArea;
use App\Models\Paper;
use App\Models\Term;
use App\Models\User;
use App\Services\SitemapService;
use Database\Seeders\CurriculumTaxonomySeeder;
use Database\Seeders\DemoPaperSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndSitemapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SiteSettingSeeder::class, CurriculumTaxonomySeeder::class, DemoPaperSeeder::class]);
    }

    public function test_robots_txt_disallows_admin_search_and_download_and_links_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Disallow: /admin', false);
        $response->assertSee('Disallow: /search', false);
        $response->assertSee('Disallow: /download/', false);
        $response->assertSee('Sitemap: https://darasahub.com/sitemap.xml', false);
    }

    public function test_unready_landing_page_is_noindex_and_search_is_always_noindex(): void
    {
        $this->get('/grade-5/kiswahili/term-2/end-term-exams')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical"', false);

        $this->get('/search?q=kiswahili&grade=grade-5')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_sitemap_includes_only_ready_pages_and_never_demo_papers(): void
    {
        $grade = Grade::query()->where('slug', 'grade-5')->firstOrFail();
        $area = LearningArea::query()->where('slug', 'kiswahili')->firstOrFail();
        $term = Term::query()->where('slug', 'term-2')->firstOrFail();
        $examType = ExamType::query()->where('slug', 'end-term-exams')->firstOrFail();
        $reviewer = User::factory()->reviewer()->create();

        $paper = Paper::factory()->publishedReady()->create([
            'title' => 'Grade 5 Kiswahili Term 2 End Term 2025 Verified',
            'slug' => 'grade-5-kiswahili-term-2-end-term-2025-verified',
            'grade_id' => $grade->id,
            'learning_area_id' => $area->id,
            'term_id' => $term->id,
            'exam_type_id' => $examType->id,
            'year' => 2025,
        ]);

        LandingPageContent::factory()->approvedReady($reviewer)->create([
            'url_path' => '/grade-5/kiswahili/term-2/end-term-exams',
            'page_type' => LandingPageContent::TYPE_TERM_LANDING,
            'intro' => implode(' ', array_fill(0, 90, 'kiswahili')),
        ]);

        $result = app(SitemapService::class)->generate();
        $this->assertGreaterThan(8, $result['total_urls']);

        $termSitemap = file_get_contents(public_path('sitemaps/sitemap-term-landings.xml'));
        $paperSitemap = file_get_contents(public_path('sitemaps/sitemap-papers.xml'));

        $this->assertStringContainsString('/grade-5/kiswahili/term-2/end-term-exams', (string) $termSitemap);
        $this->assertStringContainsString($paper->slug, (string) $paperSitemap);
        $this->assertStringNotContainsString('-demo-', (string) $paperSitemap);
    }

    public function test_draft_and_demo_paper_urls_return_404(): void
    {
        $demo = Paper::query()->where('is_demo', true)->where('category', Paper::CATEGORY_TERM)->with(['grade', 'learningArea', 'term', 'examType'])->firstOrFail();

        $this->get($demo->url_path)->assertNotFound();
    }
}
