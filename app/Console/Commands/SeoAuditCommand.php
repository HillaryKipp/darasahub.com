<?php

namespace App\Console\Commands;

use App\Models\LandingPageContent;
use App\Models\Paper;
use App\Services\ReadinessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeoAuditCommand extends Command
{
    protected $signature = 'seo:audit';

    protected $description = 'Audit landing pages missing approved intros, single-paper landing pages, and pages failing readiness.';

    public function handle(ReadinessService $readiness): int
    {
        $this->info('=== DarasaHub SEO & Content-Readiness Audit ===');

        // 1. Non-demo landing_page_content failing readiness
        $allContent = LandingPageContent::query()->nonDemo()->orderBy('url_path')->get();
        $failingContent = [];
        $missingIntro = [];

        foreach ($allContent as $content) {
            $result = $readiness->evaluate($content);
            if (! $result->ready) {
                $failingContent[] = [$content->url_path, $content->page_type, $content->status, $result->summary()];
            }
            if ($content->status === LandingPageContent::STATUS_APPROVED && $content->intro_word_count < 80 && in_array($content->page_type, [
                LandingPageContent::TYPE_TERM_LANDING,
                LandingPageContent::TYPE_NATIONAL_SUBJECT,
                LandingPageContent::TYPE_NATIONAL_YEAR,
            ], true)) {
                $missingIntro[] = [$content->url_path, $content->intro_word_count];
            }
        }

        $this->newLine();
        $this->line('1. Landing Pages Failing Readiness ('.count($failingContent).'):');
        if ($failingContent !== []) {
            $this->table(['URL Path', 'Type', 'Status', 'Failure Reasons'], $failingContent);
        } else {
            $this->info('   None.');
        }

        $this->newLine();
        $this->line('2. Approved Landing Pages Below Intro Word Threshold ('.count($missingIntro).'):');
        if ($missingIntro !== []) {
            $this->table(['URL Path', 'Word Count'], $missingIntro);
        } else {
            $this->info('   None.');
        }

        // 3. Term landing combinations with only 1 verified paper
        $singlePaperTerm = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_TERM)
            ->select('grade_id', 'learning_area_id', 'term_id', 'exam_type_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('grade_id', 'learning_area_id', 'term_id', 'exam_type_id')
            ->having('cnt', '=', 1)
            ->get();

        $this->newLine();
        $this->line('3. Verified Term Landing Pages With Only 1 Paper: '.$singlePaperTerm->count());

        // 4. Published papers failing readiness (published but noindex)
        $publishedFailingPapers = Paper::query()
            ->publishedPublic()
            ->get()
            ->filter(fn (Paper $p) => ! $readiness->evaluatePaper($p)->ready);

        $this->line('4. Published Papers Currently Noindex (failing paper readiness): '.$publishedFailingPapers->count());

        return self::SUCCESS;
    }
}
