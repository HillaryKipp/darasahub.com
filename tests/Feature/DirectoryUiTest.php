<?php

namespace Tests\Feature;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\Term;
use Database\Seeders\CurriculumTaxonomySeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectoryUiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SiteSettingSeeder::class, CurriculumTaxonomySeeder::class]);
    }

    public function test_homepage_cbc_accordion_displays_all_terms_with_checked_and_unchecked_states(): void
    {
        $grade = Grade::query()->where('slug', 'grade-5')->firstOrFail();
        $area = LearningArea::query()->where('slug', 'kiswahili')->firstOrFail();
        $term1 = Term::query()->where('slug', 'term-1')->firstOrFail();
        $endTerm = ExamType::query()->where('slug', 'end-term-exams')->firstOrFail();

        Paper::factory()->publishedReady()->create([
            'title' => 'Grade 5 Kiswahili Term 1 End Term Exam 2025',
            'slug' => 'grade-5-kiswahili-term-1-end-term-exam-2025',
            'grade_id' => $grade->id,
            'learning_area_id' => $area->id,
            'term_id' => $term1->id,
            'exam_type_id' => $endTerm->id,
            'year' => 2025,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Term 1 Exams');
        $response->assertSee('Term 2 Exams');
        $response->assertSee('Term 3 Exams');
        // Term 1 is checked & clickable; Term 2 and Term 3 remain unchecked
        $response->assertSee('href="/grade-5/kiswahili/term-1"', false);
        $response->assertDontSee('href="/grade-5/kiswahili/term-2"', false);
        $response->assertDontSee('href="/grade-5/kiswahili/term-3"', false);
        $response->assertSee('data-status="checked"', false);
        $response->assertSee('data-status="unchecked"', false);
        // Latest / Most Downloaded cards are concise with Download button and no Source/Open Paper clutter
        $response->assertSee('Download');
        $response->assertDontSee('Open Paper');
        $response->assertDontSee('Source:');
    }

    public function test_term_hub_displays_all_exam_types_and_bundles_papers_with_open_and_download(): void
    {
        $grade = Grade::query()->where('slug', 'grade-5')->firstOrFail();
        $area = LearningArea::query()->where('slug', 'kiswahili')->firstOrFail();
        $term2 = Term::query()->where('slug', 'term-2')->firstOrFail();
        $endTerm = ExamType::query()->where('slug', 'end-term-exams')->firstOrFail();

        $paper = Paper::factory()->publishedReady()->create([
            'title' => 'Grade 5 Kiswahili Term 2 End Term Exam 2025',
            'slug' => 'grade-5-kiswahili-term-2-end-term-exam-2025',
            'grade_id' => $grade->id,
            'learning_area_id' => $area->id,
            'term_id' => $term2->id,
            'exam_type_id' => $endTerm->id,
            'year' => 2025,
            'provider' => Paper::PROVIDER_GOOGLE_DRIVE,
            'external_file_id' => 'abc123XYZdriveID',
            'file_url' => 'https://drive.google.com/uc?export=download&id=abc123XYZdriveID',
        ]);

        $response = $this->get('/grade-5/kiswahili/term-2');

        $response->assertOk();
        // All 3 exam types shown
        $response->assertSee('Opener Exams');
        $response->assertSee('Midterm Exams');
        $response->assertSee('End Term Exams');
        $response->assertSee('data-status="checked"', false);
        $response->assertSee('data-status="unchecked"', false);
        // Bundled paper directly on the term hub with clickable title view link (not forced download) and Download button
        $response->assertSee($paper->title);
        $response->assertSee('https://drive.google.com/file/d/abc123XYZdriveID/view', false);
        $response->assertSee('/download/'.$paper->id, false);
        $response->assertDontSee('Open Paper');
        $response->assertSee('Download');
        // Grade Learning Areas section replaces Switch Term
        $response->assertSee('Grade 5 Learning Areas');
        $response->assertDontSee('Switch Term');
    }

    public function test_term_landing_displays_paragraph_and_open_plus_download_actions(): void
    {
        $grade = Grade::query()->where('slug', 'grade-5')->firstOrFail();
        $area = LearningArea::query()->where('slug', 'kiswahili')->firstOrFail();
        $term2 = Term::query()->where('slug', 'term-2')->firstOrFail();
        $endTerm = ExamType::query()->where('slug', 'end-term-exams')->firstOrFail();

        $paper = Paper::factory()->publishedReady()->create([
            'title' => 'Grade 5 Kiswahili Term 2 End Term Exam 2024',
            'slug' => 'grade-5-kiswahili-term-2-end-term-exam-2024',
            'grade_id' => $grade->id,
            'learning_area_id' => $area->id,
            'term_id' => $term2->id,
            'exam_type_id' => $endTerm->id,
            'year' => 2024,
            'provider' => Paper::PROVIDER_GOOGLE_DRIVE,
            'external_file_id' => 'xyz987DriveFile',
            'file_url' => 'https://drive.google.com/uc?export=download&id=xyz987DriveFile',
        ]);

        $response = $this->get('/grade-5/kiswahili/term-2/end-term-exams');

        $response->assertOk();
        $response->assertSee('Opener Exams');
        $response->assertSee('Midterm Exams');
        $response->assertSee('End Term Exams');
        $response->assertSee($paper->title);
        $response->assertSee('https://drive.google.com/file/d/xyz987DriveFile/view', false);
        $response->assertSee('/download/'.$paper->id, false);
        $response->assertDontSee('Open Paper');
        $response->assertSee('Download');
    }

    public function test_kcse_subject_and_year_pages_are_concise_and_hold_papers_directly(): void
    {
        $kcse = NationalExam::query()->where('slug', 'kcse')->firstOrFail();
        $agriculture = LearningArea::query()->where('slug', 'agriculture')->firstOrFail();

        $paper = Paper::factory()->publishedReady()->create([
            'category' => Paper::CATEGORY_NATIONAL,
            'curriculum' => Paper::CURRICULUM_LEGACY_844,
            'grade_id' => null,
            'term_id' => null,
            'exam_type_id' => null,
            'national_exam_id' => $kcse->id,
            'learning_area_id' => $agriculture->id,
            'year' => 2023,
            'paper_number' => 'Paper 1',
            'title' => 'KCSE 2023 Agriculture Paper 1 Past Paper',
            'slug' => 'kcse-2023-agriculture-paper-1-past-paper',
            'description' => 'Download the KCSE 2023 Agriculture Paper 1 official paper. Free revision resource for Kenyan secondary school students.',
            'source_name' => 'KNEC / DarasaHub KCSE Archive',
            'provider' => Paper::PROVIDER_GOOGLE_DRIVE,
            'external_file_id' => 'kcse2023AgriP1',
            'file_url' => 'https://drive.google.com/uc?export=download&id=kcse2023AgriP1',
        ]);

        // Subject landing page is concise and links paper titles to the year page (not Google Drive)
        $subjectResponse = $this->get('/kcse/agriculture-past-papers');
        $subjectResponse->assertOk();
        $subjectResponse->assertSee('KCSE 2023 Agriculture Paper 1 Past Paper');
        $subjectResponse->assertSee('href="/kcse/agriculture-past-papers/2023"', false);
        $subjectResponse->assertDontSee('https://drive.google.com/file/d/kcse2023AgriP1/view', false);
        $subjectResponse->assertSee('/download/'.$paper->id, false);
        $subjectResponse->assertDontSee('Free revision resource for Kenyan secondary school students');
        $subjectResponse->assertDontSee('Source: KNEC / DarasaHub KCSE Archive');
        $subjectResponse->assertSee('KCSE Subjects');

        // Year landing page holds the paper directly with preview open by default
        $yearResponse = $this->get('/kcse/agriculture-past-papers/2023');
        $yearResponse->assertOk();
        $yearResponse->assertSee('KCSE 2023 Agriculture Paper 1 Past Paper');
        $yearResponse->assertSee('https://drive.google.com/file/d/kcse2023AgriP1/view', false);
        $yearResponse->assertSee('https://drive.google.com/file/d/kcse2023AgriP1/preview', false);
        $yearResponse->assertSee('showPreview: true', false);
        $yearResponse->assertSee('/download/'.$paper->id, false);
        $yearResponse->assertDontSee('Free revision resource for Kenyan secondary school students');
    }

    public function test_about_and_contact_pages_display_darasahub_holdings_company_details(): void
    {
        $about = $this->get('/about');
        $about->assertOk();
        $about->assertSee('images/logo.png', false);
        $about->assertSee('favicon.ico', false);
        $about->assertSee('apple-touch-icon.png', false);
        $about->assertSee('Darasahub Holdings Limited');
        $about->assertSee('We bring the product design, engineering, and delivery. You bring the learners, content, and reach.');
        $about->assertSee('https://play.google.com/store/apps/details?id=com.darasadrive.darasadrive_mobile', false);
        $about->assertSee('https://cbcschemes.co.ke/', false);
        $about->assertSee('https://easytvethub.com', false);

        $contact = $this->get('/contact');
        $contact->assertOk();
        $contact->assertSee('Darasahub Holdings Limited');
        $contact->assertSee('+254 734 232 994');
        $contact->assertSee('tel:+254734232994', false);
        $contact->assertSee('masomo@darasahub.com');
        $contact->assertSee('1st Floor, Karen Professionals');
        $contact->assertSee('P.O. Box 20904');
    }
}
