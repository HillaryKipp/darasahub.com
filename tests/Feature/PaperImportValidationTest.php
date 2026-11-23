<?php

namespace Tests\Feature;

use App\Models\ImportJob;
use App\Models\Paper;
use App\Services\PaperImportService;
use Database\Seeders\CurriculumTaxonomySeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaperImportValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SiteSettingSeeder::class, CurriculumTaxonomySeeder::class]);
    }

    public function test_import_validates_rows_catches_errors_and_never_auto_publishes(): void
    {
        $csv = implode("\n", [
            'grade,learning_area,term,exam_type,year,title,source,source_url,file_url,file_type,category,national_exam,curriculum',
            'grade-5,kiswahili,term-2,end-term-exams,2025,Grade 5 Kiswahili T2 2025,County Archive,https://example.org,https://example.org/p1.pdf,question_paper,term,,cbc',
            // Invalid row: bad grade + bad year + bad URL
            'grade-99,kiswahili,term-2,end-term-exams,1800,Bad Row,County Archive,,not-a-url,question_paper,term,,cbc',
            // Duplicate file_url of row 1
            'grade-5,kiswahili,term-2,end-term-exams,2025,Duplicate URL Row,County Archive,https://example.org,https://example.org/p1.pdf,question_paper,term,,cbc',
        ]);

        $tmpFile = tempnam(sys_get_temp_dir(), 'cbc_import_').'.csv';
        file_put_contents($tmpFile, $csv);

        try {
            $service = app(PaperImportService::class);
            $job = $service->preview($tmpFile, 'test.csv');

            $this->assertSame(ImportJob::STATUS_PREVIEWED, $job->status);
            $this->assertSame(3, $job->total_rows);
            $this->assertSame(1, $job->valid_rows);
            $this->assertSame(2, $job->error_rows);
            $this->assertSame(0, Paper::query()->count());

            $committed = $service->commit($job);
            $this->assertSame(1, $committed);

            $paper = Paper::query()->firstOrFail();
            $this->assertSame(Paper::PUB_DRAFT, $paper->publication_status);
            $this->assertSame(Paper::VERIFICATION_UNVERIFIED, $paper->verification_status);
            $this->assertSame(Paper::PERMISSION_PENDING, $paper->permission_status);
            $this->assertFalse($paper->is_demo);
        } finally {
            @unlink($tmpFile);
        }
    }
}
