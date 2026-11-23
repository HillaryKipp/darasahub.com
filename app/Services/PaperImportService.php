<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ExamType;
use App\Models\Grade;
use App\Models\GradeLearningArea;
use App\Models\ImportJob;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelReader;

class PaperImportService
{
    /**
     * Streams a CSV/XLSX file, validates every row, and saves a staged preview in import_jobs.
     * Never writes to `papers` until commit() is explicitly invoked.
     */
    public function preview(string $fullPath, string $originalFilename, ?int $userId = null): ImportJob
    {
        $grades = collect();
        foreach (Grade::query()->get() as $g) {
            $grades->put(strtolower($g->slug), $g);
            $grades->put(strtolower($g->name), $g);
        }

        $areas = collect();
        foreach (LearningArea::query()->get() as $a) {
            $areas->put(strtolower($a->slug), $a);
            $areas->put(strtolower($a->name), $a);
        }

        $terms = collect();
        foreach (Term::query()->get() as $t) {
            $terms->put(strtolower($t->slug), $t);
            $terms->put(strtolower($t->name), $t);
        }

        $examTypes = collect();
        foreach (ExamType::query()->get() as $e) {
            $examTypes->put(strtolower($e->slug), $e);
            $examTypes->put(strtolower($e->name), $e);
            $examTypes->put(strtolower($e->short_label), $e);
        }

        $nationalExams = collect();
        foreach (NationalExam::query()->get() as $n) {
            $nationalExams->put(strtolower($n->slug), $n);
            $nationalExams->put(strtolower($n->name), $n);
        }

        $existingSlugs = Paper::query()->pluck('slug')->flip()->all();
        $existingFileUrls = Paper::query()->whereNotNull('file_url')->pluck('file_url')->flip()->all();

        $seenSlugs = [];
        $seenUrls = [];
        $validRows = [];
        $errors = [];
        $totalRows = 0;

        $reader = SimpleExcelReader::create($fullPath);
        $reader->getRows()->each(function (array $rawRow) use (
            &$totalRows,
            &$validRows,
            &$errors,
            &$seenSlugs,
            &$seenUrls,
            $grades,
            $areas,
            $terms,
            $examTypes,
            $nationalExams,
            $existingSlugs,
            $existingFileUrls
        ): void {
            $totalRows++;
            $rowNumber = $totalRows + 1; // +1 for header row

            // Normalize header keys to lowercase snake_case
            $row = [];
            foreach ($rawRow as $k => $v) {
                $key = strtolower(trim(str_replace([' ', '-'], '_', (string) $k)));
                $row[$key] = is_string($v) ? trim($v) : $v;
            }

            $rowErrors = [];

            $category = strtolower((string) ($row['category'] ?? 'term'));
            if (! in_array($category, [Paper::CATEGORY_TERM, Paper::CATEGORY_NATIONAL], true)) {
                $rowErrors[] = "Invalid category '{$category}' (expected 'term' or 'national').";
            }

            $areaKey = strtolower((string) ($row['learning_area'] ?? ''));
            /** @var LearningArea|null $area */
            $area = $areas->get($areaKey) ?? $areas->get(Str::slug($areaKey));
            if (! $area) {
                $rowErrors[] = "Unknown learning_area '{$row['learning_area']}'.";
            }

            /** @var Grade|null $grade */
            $grade = null;
            /** @var Term|null $term */
            $term = null;
            /** @var ExamType|null $examType */
            $examType = null;
            /** @var NationalExam|null $nationalExam */
            $nationalExam = null;

            if ($category === Paper::CATEGORY_TERM) {
                $gradeKey = strtolower((string) ($row['grade'] ?? ''));
                $grade = $grades->get($gradeKey) ?? $grades->get(Str::slug($gradeKey));
                if (! $grade) {
                    $rowErrors[] = "Unknown grade '{$row['grade']}'.";
                }

                $termKey = strtolower((string) ($row['term'] ?? ''));
                $term = $terms->get($termKey) ?? $terms->get(Str::slug($termKey));
                if (! $term) {
                    $rowErrors[] = "Unknown term '{$row['term']}'.";
                }

                $examTypeKey = strtolower((string) ($row['exam_type'] ?? ''));
                $examType = $examTypes->get($examTypeKey) ?? $examTypes->get(Str::slug($examTypeKey));
                if (! $examType) {
                    $rowErrors[] = "Unknown exam_type '{$row['exam_type']}'.";
                }

                if ($grade && $area) {
                    $mapped = GradeLearningArea::query()
                        ->where('grade_id', $grade->id)
                        ->where('learning_area_id', $area->id)
                        ->exists();
                    if (! $mapped) {
                        $rowErrors[] = "Learning area '{$area->name}' is not mapped to '{$grade->name}' in grade_learning_area.";
                    }
                }
            } elseif ($category === Paper::CATEGORY_NATIONAL) {
                $examKey = strtolower((string) ($row['national_exam'] ?? ''));
                $nationalExam = $nationalExams->get($examKey) ?? $nationalExams->get(Str::slug($examKey));
                if (! $nationalExam) {
                    $rowErrors[] = "Unknown national_exam '{$row['national_exam']}'.";
                } else {
                    $grade = $nationalExam->grade;
                }
            }

            $year = (int) ($row['year'] ?? 0);
            $maxYear = (int) date('Y') + 1;
            if ($year < 1990 || $year > $maxYear) {
                $rowErrors[] = "Invalid year '{$row['year']}' (must be 1990–{$maxYear}).";
            }

            $fileType = strtolower((string) ($row['file_type'] ?? Paper::FILE_TYPE_QUESTION_PAPER));
            if ($fileType === '') {
                $fileType = Paper::FILE_TYPE_QUESTION_PAPER;
            }
            if (! in_array($fileType, [Paper::FILE_TYPE_QUESTION_PAPER, Paper::FILE_TYPE_MARKING_SCHEME], true)) {
                $rowErrors[] = "Invalid file_type '{$fileType}' (expected 'question_paper' or 'marking_scheme').";
            }

            $curriculum = strtolower((string) ($row['curriculum'] ?? Paper::CURRICULUM_CBC));
            if ($curriculum === '') {
                $curriculum = Paper::CURRICULUM_CBC;
            }
            if (! in_array($curriculum, [Paper::CURRICULUM_CBC, Paper::CURRICULUM_LEGACY_844], true)) {
                $rowErrors[] = "Invalid curriculum '{$curriculum}' (expected 'cbc' or 'legacy_844').";
            }

            $fileUrl = (string) ($row['file_url'] ?? '');
            if ($fileUrl === '' || ! filter_var($fileUrl, FILTER_VALIDATE_URL)) {
                $rowErrors[] = 'Invalid or missing file_url.';
            }

            $sourceUrl = (string) ($row['source_url'] ?? '');
            if ($sourceUrl !== '' && ! filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
                $rowErrors[] = "Invalid source_url '{$sourceUrl}'.";
            }

            $sourceName = trim((string) ($row['source'] ?? ''));
            if ($sourceName === '') {
                $rowErrors[] = 'Missing source attribution (source column).';
            }

            // Auto-generate title & slug if omitted
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '' && $area && $year >= 1990) {
                $typeSuffix = $fileType === Paper::FILE_TYPE_MARKING_SCHEME ? 'Marking Scheme' : 'Exam Paper';
                $title = $category === Paper::CATEGORY_NATIONAL && $nationalExam
                    ? "{$nationalExam->name} {$area->name} {$year} {$typeSuffix}"
                    : trim(($grade?->name ?? '')." {$area->name} ".($term?->name ?? '').' '.($examType?->short_label ?? '')." {$year} {$typeSuffix}");
            }

            $slug = Str::slug((string) ($row['slug'] ?? $title));
            if ($slug === '') {
                $rowErrors[] = 'Could not generate a valid slug.';
            } else {
                if (isset($existingSlugs[$slug]) || isset($seenSlugs[$slug])) {
                    $rowErrors[] = "Duplicate slug '{$slug}'.";
                }
            }

            if ($fileUrl !== '' && (isset($existingFileUrls[$fileUrl]) || isset($seenUrls[$fileUrl]))) {
                $rowErrors[] = "Duplicate file_url '{$fileUrl}'.";
            }

            if ($rowErrors !== []) {
                $errors[] = [
                    'row' => $rowNumber,
                    'title' => $title ?: '(untitled)',
                    'messages' => $rowErrors,
                ];

                return;
            }

            $seenSlugs[$slug] = true;
            $seenUrls[$fileUrl] = true;

            $validRows[] = [
                'row' => $rowNumber,
                'title' => $title,
                'slug' => $slug,
                'category' => $category,
                'curriculum' => $curriculum,
                'grade_id' => $grade?->id,
                'learning_area_id' => $area->id,
                'term_id' => $term?->id,
                'exam_type_id' => $examType?->id,
                'national_exam_id' => $nationalExam?->id,
                'year' => $year,
                'paper_number' => ($row['paper_number'] ?? '') !== '' ? (string) $row['paper_number'] : null,
                'set_label' => ($row['set_label'] ?? '') !== '' ? (string) $row['set_label'] : null,
                'file_type' => $fileType,
                'description' => ($row['description'] ?? '') !== '' ? (string) $row['description'] : null,
                'source_name' => $sourceName,
                'source_url' => $sourceUrl !== '' ? $sourceUrl : null,
                'file_url' => $fileUrl,
            ];
        });

        $job = ImportJob::create([
            'user_id' => $userId ?? auth()->id(),
            'original_filename' => $originalFilename,
            'stored_path' => $fullPath,
            'status' => ImportJob::STATUS_PREVIEWED,
            'total_rows' => $totalRows,
            'valid_rows' => count($validRows),
            'error_rows' => count($errors),
            'preview_data' => $validRows,
            'errors' => $errors,
        ]);

        AuditLog::record('import.previewed', $job, null, [
            'filename' => $originalFilename,
            'total_rows' => $totalRows,
            'valid_rows' => count($validRows),
            'error_rows' => count($errors),
        ], userId: $userId);

        return $job;
    }

    /**
     * Commits all validated rows from a previewed ImportJob.
     * Non-negotiable (Section 4 & 16): imported papers ALWAYS start as draft, unverified, pending permission (never auto-published).
     */
    public function commit(ImportJob $job, ?int $userId = null): int
    {
        if ($job->status !== ImportJob::STATUS_PREVIEWED) {
            throw new \DomainException("Import job #{$job->id} cannot be committed in status '{$job->status}'.");
        }

        $rows = is_array($job->preview_data) ? $job->preview_data : [];
        if ($rows === []) {
            $job->update([
                'status' => ImportJob::STATUS_COMMITTED,
                'committed_count' => 0,
                'committed_at' => now(),
            ]);

            return 0;
        }

        $actorId = $userId ?? auth()->id();
        $committed = 0;

        DB::transaction(function () use ($rows, $actorId, &$committed): void {
            foreach ($rows as $row) {
                Paper::create([
                    'title' => $row['title'],
                    'slug' => $row['slug'],
                    'category' => $row['category'],
                    'curriculum' => $row['curriculum'],
                    'grade_id' => $row['grade_id'],
                    'learning_area_id' => $row['learning_area_id'],
                    'term_id' => $row['term_id'],
                    'exam_type_id' => $row['exam_type_id'],
                    'national_exam_id' => $row['national_exam_id'],
                    'year' => $row['year'],
                    'paper_number' => $row['paper_number'],
                    'set_label' => $row['set_label'],
                    'file_type' => $row['file_type'],
                    'description' => $row['description'],
                    'source_name' => $row['source_name'],
                    'source_url' => $row['source_url'],
                    'permission_status' => Paper::PERMISSION_PENDING,
                    'provider' => Paper::PROVIDER_EXTERNAL_LINK,
                    'file_url' => $row['file_url'],
                    'availability' => Paper::AVAILABILITY_AVAILABLE,
                    'verification_status' => Paper::VERIFICATION_UNVERIFIED,
                    'publication_status' => Paper::PUB_DRAFT,
                    'is_demo' => false,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);
                $committed++;
            }
        });

        $job->update([
            'status' => ImportJob::STATUS_COMMITTED,
            'committed_count' => $committed,
            'committed_at' => now(),
        ]);

        AuditLog::record('import.committed', $job, null, [
            'committed_count' => $committed,
        ], userId: $actorId);

        return $committed;
    }
}
