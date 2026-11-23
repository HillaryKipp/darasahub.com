<?php

namespace App\Console\Commands;

use App\Services\PaperImportService;
use Illuminate\Console\Command;

class ImportPapersCommand extends Command
{
    protected $signature = 'papers:import {file : Path to the CSV or XLSX file} {--commit : Commit valid rows as drafts after preview}';

    protected $description = 'Validate and preview a CSV/XLSX papers import. Pass --commit to insert valid rows as draft/unverified (never auto-publishes).';

    public function handle(PaperImportService $importer): int
    {
        $path = (string) $this->argument('file');
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $job = $importer->preview($path, basename($path));

        $this->info("Import Job #{$job->id} previewed: {$job->total_rows} total, {$job->valid_rows} valid, {$job->error_rows} error(s).");

        if ($job->error_rows > 0) {
            $this->warn('Validation errors:');
            foreach (array_slice($job->errors ?? [], 0, 25) as $err) {
                $this->line("  Row {$err['row']} ({$err['title']}): ".implode(' | ', $err['messages']));
            }
        }

        if (! $this->option('commit')) {
            $this->comment('Dry-run preview only. Re-run with --commit to insert valid rows as draft/unverified.');

            return self::SUCCESS;
        }

        $committed = $importer->commit($job);
        $this->info("Committed {$committed} paper(s) as draft & unverified (never auto-published).");

        return self::SUCCESS;
    }
}
