<?php

namespace App\Console\Commands;

use App\Models\Paper;
use App\Services\LinkCheckerService;
use Illuminate\Console\Command;

class LinksCheckCommand extends Command
{
    protected $signature = 'links:check {--limit=50 : Maximum papers to check per run}';

    protected $description = 'Run scheduled link checks on non-demo papers and record results in link_checks.';

    public function handle(LinkCheckerService $checker): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $papers = Paper::query()
            ->nonDemo()
            ->whereNotNull('file_url')
            ->where('file_url', '!=', '')
            ->orderByRaw('last_checked_at IS NOT NULL, last_checked_at ASC')
            ->limit($limit)
            ->get();

        if ($papers->isEmpty()) {
            $this->info('No non-demo papers with file URLs to check.');

            return self::SUCCESS;
        }

        $counts = [];
        foreach ($papers as $paper) {
            $check = $checker->checkPaper($paper);
            $counts[$check->result] = ($counts[$check->result] ?? 0) + 1;
        }

        $summary = collect($counts)->map(fn ($n, $k) => "{$k}={$n}")->implode(', ');
        $this->info("Checked {$papers->count()} paper(s): {$summary}.");

        return self::SUCCESS;
    }
}
