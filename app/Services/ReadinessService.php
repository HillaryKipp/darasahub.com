<?php

namespace App\Services;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\GradeLearningArea;
use App\Models\LandingPageContent;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\SiteSetting;
use App\Models\Term;
use App\Support\ReadinessResult;
use Illuminate\Support\Collection;

class ReadinessService
{
    /**
     * Single source of truth for page indexability, sitemap inclusion,
     * public internal-link visibility, publish guards, and dashboard audits.
     *
     * @param  Paper|LandingPageContent|array<string, mixed>  $page
     */
    public function evaluate(Paper|LandingPageContent|array $page): ReadinessResult
    {
        if ($page instanceof Paper) {
            return $this->evaluatePaper($page);
        }

        if ($page instanceof LandingPageContent) {
            return $this->evaluateLandingContentRecord($page);
        }

        $type = $page['type'] ?? '';

        return match ($type) {
            LandingPageContent::TYPE_TERM_LANDING => $this->evaluateTermLanding(
                $page['grade'],
                $page['learning_area'],
                $page['term'],
                $page['exam_type'],
                $page['content'] ?? null,
            ),
            LandingPageContent::TYPE_NATIONAL_SUBJECT => $this->evaluateNationalSubject(
                $page['national_exam'],
                $page['learning_area'],
                $page['content'] ?? null,
            ),
            LandingPageContent::TYPE_NATIONAL_YEAR => $this->evaluateNationalYear(
                $page['national_exam'],
                $page['learning_area'],
                (int) $page['year'],
                $page['content'] ?? null,
            ),
            LandingPageContent::TYPE_GRADE_HUB => $this->evaluateGradeHub(
                $page['grade'],
                $page['content'] ?? null,
            ),
            LandingPageContent::TYPE_LEARNING_AREA_HUB => $this->evaluateLearningAreaHub(
                $page['grade'],
                $page['learning_area'],
                $page['content'] ?? null,
            ),
            LandingPageContent::TYPE_TERM_HUB => $this->evaluateTermHub(
                $page['grade'],
                $page['learning_area'],
                $page['term'],
                $page['content'] ?? null,
            ),
            LandingPageContent::TYPE_NATIONAL_HUB => $this->evaluateNationalHub(
                $page['national_exam'],
                $page['content'] ?? null,
            ),
            default => new ReadinessResult(false, ['Unknown page type for readiness evaluation.']),
        };
    }

    public function evaluatePaper(Paper $paper): ReadinessResult
    {
        $reasons = [];

        if ($paper->is_demo) {
            $reasons[] = 'Demo records (is_demo = true) can never be published or indexed.';
        }

        if ($paper->publication_status !== Paper::PUB_PUBLISHED) {
            $reasons[] = 'Paper is not published.';
        }

        if ($paper->permission_status !== Paper::PERMISSION_PERMITTED) {
            $reasons[] = 'Paper permission status is not permitted.';
        }

        if (! $paper->hasWorkingResource()) {
            $reasons[] = 'Paper does not have a verified, available file resource.';
        }

        if (empty(trim((string) $paper->source_name))) {
            $reasons[] = 'Missing source attribution (source_name).';
        }

        // Verified identity check
        if ($paper->category === Paper::CATEGORY_TERM) {
            if (! $paper->grade_id || ! $paper->learning_area_id || ! $paper->term_id || ! $paper->exam_type_id || ! $paper->year) {
                $reasons[] = 'Incomplete term paper identity (grade, learning area, term, exam type, and year required).';
            }
        } elseif ($paper->category === Paper::CATEGORY_NATIONAL) {
            if (! $paper->national_exam_id || ! $paper->learning_area_id || ! $paper->year) {
                $reasons[] = 'Incomplete national paper identity (national exam, learning area, and year required).';
            }
        } else {
            $reasons[] = 'Invalid paper category.';
        }

        // Useful context beyond title and download button (Section 8)
        $minDescWords = (int) SiteSetting::getValue('readiness_min_paper_description_words', 15);
        $descWords = str_word_count(trim(strip_tags((string) $paper->description)));
        $hasExtraContext = $descWords >= $minDescWords
            || (! empty($paper->related_paper_id) && $descWords >= 5)
            || (! empty($paper->file_size) && $descWords >= 8);

        if (! $hasExtraContext) {
            $reasons[] = "Paper lacks sufficient contextual description (needs at least {$minDescWords} words or supporting metadata).";
        }

        return new ReadinessResult($reasons === [], $reasons);
    }

    public function evaluateTermLanding(
        Grade $grade,
        LearningArea $learningArea,
        Term $term,
        ExamType $examType,
        ?LandingPageContent $content = null
    ): ReadinessResult {
        $reasons = [];

        if ($grade->is_demo || ! $grade->is_active || $learningArea->is_demo || ! $learningArea->is_active) {
            $reasons[] = 'Grade or learning area is inactive or demo.';
        }

        $verifiedPivot = GradeLearningArea::query()
            ->where('grade_id', $grade->id)
            ->where('learning_area_id', $learningArea->id)
            ->verified()
            ->exists();

        if (! $verifiedPivot) {
            $reasons[] = 'Learning area is not verified for this grade in the curriculum pivot.';
        }

        $minPapers = (int) SiteSetting::getValue('readiness_min_landing_papers', 1);
        $readyPaperCount = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_TERM)
            ->where('grade_id', $grade->id)
            ->where('learning_area_id', $learningArea->id)
            ->where('term_id', $term->id)
            ->where('exam_type_id', $examType->id)
            ->count();

        if ($readyPaperCount < $minPapers) {
            $reasons[] = "Needs at least {$minPapers} verified, published, permitted paper(s); found {$readyPaperCount}.";
        }

        $urlPath = sprintf('/%s/%s/%s/%s', $grade->slug, $learningArea->slug, $term->slug, $examType->slug);
        $content ??= $this->findContent($urlPath);
        $minIntroWords = (int) SiteSetting::getValue('readiness_min_landing_intro_words', 0);

        $this->checkEditorialIntro($content, $minIntroWords, $reasons);

        return new ReadinessResult($reasons === [], $reasons);
    }

    public function evaluateNationalSubject(
        NationalExam $exam,
        LearningArea $learningArea,
        ?LandingPageContent $content = null
    ): ReadinessResult {
        $reasons = [];

        if ($exam->is_demo || ! $exam->is_active || $learningArea->is_demo || ! $learningArea->is_active) {
            $reasons[] = 'National exam or learning area is inactive or demo.';
        }

        $minPapers = (int) SiteSetting::getValue('readiness_min_landing_papers', 1);
        $readyPaperCount = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->where('national_exam_id', $exam->id)
            ->where('learning_area_id', $learningArea->id)
            ->count();

        if ($readyPaperCount < $minPapers) {
            $reasons[] = "Needs at least {$minPapers} verified, published, permitted paper(s); found {$readyPaperCount}.";
        }

        $urlPath = sprintf('/%s/%s-past-papers', $exam->slug, $learningArea->slug);
        $content ??= $this->findContent($urlPath);
        $minIntroWords = (int) SiteSetting::getValue('readiness_min_landing_intro_words', 0);

        $this->checkEditorialIntro($content, $minIntroWords, $reasons);

        return new ReadinessResult($reasons === [], $reasons);
    }

    public function evaluateNationalYear(
        NationalExam $exam,
        LearningArea $learningArea,
        int $year,
        ?LandingPageContent $content = null
    ): ReadinessResult {
        $reasons = [];

        if ($exam->is_demo || ! $exam->is_active || $learningArea->is_demo || ! $learningArea->is_active) {
            $reasons[] = 'National exam or learning area is inactive or demo.';
        }

        $minPapers = (int) SiteSetting::getValue('readiness_min_landing_papers', 1);
        $readyPaperCount = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->where('national_exam_id', $exam->id)
            ->where('learning_area_id', $learningArea->id)
            ->where('year', $year)
            ->count();

        if ($readyPaperCount < $minPapers) {
            $reasons[] = "Needs at least {$minPapers} verified, published, permitted paper(s) for {$year}; found {$readyPaperCount}.";
        }

        $urlPath = sprintf('/%s/%s-past-papers/%d', $exam->slug, $learningArea->slug, $year);
        $content ??= $this->findContent($urlPath);
        $minIntroWords = (int) SiteSetting::getValue('readiness_min_landing_intro_words', 0);

        $this->checkEditorialIntro($content, $minIntroWords, $reasons);

        return new ReadinessResult($reasons === [], $reasons);
    }

    public function evaluateTermHub(
        Grade $grade,
        LearningArea $learningArea,
        Term $term,
        ?LandingPageContent $content = null
    ): ReadinessResult {
        $reasons = [];
        $readyExamTypes = $this->getReadyExamTypesForTerm($grade, $learningArea, $term);

        if ($readyExamTypes->isEmpty()) {
            $reasons[] = 'No ready exam-type landing pages under this term.';
        }

        $urlPath = sprintf('/%s/%s/%s', $grade->slug, $learningArea->slug, $term->slug);
        $content ??= $this->findContent($urlPath);
        $minHubWords = (int) SiteSetting::getValue('readiness_min_hub_intro_words', 0);

        $this->checkEditorialIntro($content, $minHubWords, $reasons);

        return new ReadinessResult($reasons === [], $reasons);
    }

    public function evaluateLearningAreaHub(
        Grade $grade,
        LearningArea $learningArea,
        ?LandingPageContent $content = null
    ): ReadinessResult {
        $reasons = [];
        $hasReadyChild = false;

        foreach (Term::query()->ordered()->get() as $term) {
            if ($this->getReadyExamTypesForTerm($grade, $learningArea, $term)->isNotEmpty()) {
                $hasReadyChild = true;
                break;
            }
        }

        if (! $hasReadyChild) {
            $reasons[] = 'No ready child landing pages under this grade & learning area.';
        }

        $urlPath = sprintf('/%s/%s', $grade->slug, $learningArea->slug);
        $content ??= $this->findContent($urlPath);
        $minHubWords = (int) SiteSetting::getValue('readiness_min_hub_intro_words', 0);

        $this->checkEditorialIntro($content, $minHubWords, $reasons);

        return new ReadinessResult($reasons === [], $reasons);
    }

    public function evaluateGradeHub(Grade $grade, ?LandingPageContent $content = null): ReadinessResult
    {
        $reasons = [];

        if ($grade->is_demo || ! $grade->is_active) {
            $reasons[] = 'Grade is inactive or demo.';
        }

        $readyAreas = $this->getReadyLearningAreasForGrade($grade);

        if ($readyAreas->isEmpty()) {
            $reasons[] = 'No ready child landing pages under this grade.';
        }

        $urlPath = '/'.$grade->slug;
        $content ??= $this->findContent($urlPath);
        $minHubWords = (int) SiteSetting::getValue('readiness_min_hub_intro_words', 0);

        $this->checkEditorialIntro($content, $minHubWords, $reasons);

        return new ReadinessResult($reasons === [], $reasons);
    }

    public function evaluateNationalHub(NationalExam $exam, ?LandingPageContent $content = null): ReadinessResult
    {
        $reasons = [];

        if ($exam->is_demo || ! $exam->is_active) {
            $reasons[] = 'National exam is inactive or demo.';
        }

        $readySubjects = $this->getReadySubjectsForNationalExam($exam);

        if ($readySubjects->isEmpty()) {
            $reasons[] = 'No ready subject landing pages under this national exam.';
        }

        $urlPath = '/'.$exam->slug;
        $content ??= $this->findContent($urlPath);
        $minHubWords = (int) SiteSetting::getValue('readiness_min_hub_intro_words', 0);

        $this->checkEditorialIntro($content, $minHubWords, $reasons);

        return new ReadinessResult($reasons === [], $reasons);
    }

    public function evaluateLandingContentRecord(LandingPageContent $content): ReadinessResult
    {
        $segments = array_values(array_filter(explode('/', trim($content->url_path, '/'))));

        if ($content->page_type === LandingPageContent::TYPE_TERM_LANDING && count($segments) === 4) {
            $grade = Grade::query()->where('slug', $segments[0])->first();
            $area = LearningArea::query()->where('slug', $segments[1])->first();
            $term = Term::query()->where('slug', $segments[2])->first();
            $examType = ExamType::query()->where('slug', $segments[3])->first();

            if ($grade && $area && $term && $examType) {
                return $this->evaluateTermLanding($grade, $area, $term, $examType, $content);
            }
        }

        if ($content->page_type === LandingPageContent::TYPE_TERM_HUB && count($segments) === 3) {
            $grade = Grade::query()->where('slug', $segments[0])->first();
            $area = LearningArea::query()->where('slug', $segments[1])->first();
            $term = Term::query()->where('slug', $segments[2])->first();

            if ($grade && $area && $term) {
                return $this->evaluateTermHub($grade, $area, $term, $content);
            }
        }

        if ($content->page_type === LandingPageContent::TYPE_LEARNING_AREA_HUB && count($segments) === 2) {
            $grade = Grade::query()->where('slug', $segments[0])->first();
            $area = LearningArea::query()->where('slug', $segments[1])->first();

            if ($grade && $area) {
                return $this->evaluateLearningAreaHub($grade, $area, $content);
            }
        }

        if ($content->page_type === LandingPageContent::TYPE_GRADE_HUB && count($segments) === 1) {
            $grade = Grade::query()->where('slug', $segments[0])->first();
            if ($grade) {
                return $this->evaluateGradeHub($grade, $content);
            }
        }

        if ($content->page_type === LandingPageContent::TYPE_NATIONAL_HUB && count($segments) === 1) {
            $exam = NationalExam::query()->where('slug', $segments[0])->first();
            if ($exam) {
                return $this->evaluateNationalHub($exam, $content);
            }
        }

        if ($content->page_type === LandingPageContent::TYPE_NATIONAL_SUBJECT && count($segments) === 2) {
            $exam = NationalExam::query()->where('slug', $segments[0])->first();
            $areaSlug = preg_replace('/-past-papers$/', '', $segments[1]);
            $area = LearningArea::query()->where('slug', $areaSlug)->first();
            if ($exam && $area) {
                return $this->evaluateNationalSubject($exam, $area, $content);
            }
        }

        if ($content->page_type === LandingPageContent::TYPE_NATIONAL_YEAR && count($segments) === 3) {
            $exam = NationalExam::query()->where('slug', $segments[0])->first();
            $areaSlug = preg_replace('/-past-papers$/', '', $segments[1]);
            $area = LearningArea::query()->where('slug', $areaSlug)->first();
            if ($exam && $area && ctype_digit($segments[2])) {
                return $this->evaluateNationalYear($exam, $area, (int) $segments[2], $content);
            }
        }

        return new ReadinessResult(false, ['Could not resolve URL path to a valid curriculum entity.']);
    }

    /**
     * @return Collection<int, ExamType>
     */
    public function getReadyExamTypesForTerm(Grade $grade, LearningArea $learningArea, Term $term): Collection
    {
        $candidateIds = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_TERM)
            ->where('grade_id', $grade->id)
            ->where('learning_area_id', $learningArea->id)
            ->where('term_id', $term->id)
            ->distinct()
            ->pluck('exam_type_id')
            ->filter();

        if ($candidateIds->isEmpty()) {
            return collect();
        }

        return ExamType::query()
            ->whereIn('id', $candidateIds)
            ->ordered()
            ->get()
            ->filter(fn (ExamType $examType) => $this->evaluateTermLanding($grade, $learningArea, $term, $examType)->ready)
            ->values();
    }

    /**
     * @return Collection<int, LearningArea>
     */
    public function getReadyLearningAreasForGrade(Grade $grade): Collection
    {
        $candidateAreaIds = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_TERM)
            ->where('grade_id', $grade->id)
            ->distinct()
            ->pluck('learning_area_id')
            ->filter();

        if ($candidateAreaIds->isEmpty()) {
            return collect();
        }

        $terms = Term::query()->ordered()->get();

        return $grade->verifiedLearningAreas()
            ->whereIn('learning_areas.id', $candidateAreaIds)
            ->get()
            ->filter(function (LearningArea $area) use ($grade, $terms): bool {
                foreach ($terms as $term) {
                    if ($this->getReadyExamTypesForTerm($grade, $area, $term)->isNotEmpty()) {
                        return true;
                    }
                }

                return false;
            })
            ->values();
    }

    /**
     * @return Collection<int, LearningArea>
     */
    public function getReadySubjectsForNationalExam(NationalExam $exam): Collection
    {
        $areaIds = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->where('national_exam_id', $exam->id)
            ->distinct()
            ->pluck('learning_area_id')
            ->filter();

        if ($areaIds->isEmpty()) {
            return collect();
        }

        return LearningArea::query()
            ->active()
            ->whereIn('id', $areaIds)
            ->orderBy('name')
            ->get()
            ->filter(fn (LearningArea $area) => $this->evaluateNationalSubject($exam, $area)->ready)
            ->values();
    }

    /**
     * @return Collection<int, int>
     */
    public function getReadyYearsForNationalSubject(NationalExam $exam, LearningArea $learningArea): Collection
    {
        $years = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->where('national_exam_id', $exam->id)
            ->where('learning_area_id', $learningArea->id)
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return $years
            ->filter(fn ($year) => $this->evaluateNationalYear($exam, $learningArea, (int) $year)->ready)
            ->map(fn ($year) => (int) $year)
            ->values();
    }

    public function findContent(string $urlPath): ?LandingPageContent
    {
        $normalized = $this->normalizePath($urlPath);

        return LandingPageContent::query()
            ->where('url_path', $normalized)
            ->where('is_demo', false)
            ->first();
    }

    /**
     * Batch-build the homepage readiness structures in a small, fixed number of queries
     * (eliminating the nested Grade -> LearningArea -> Term -> ExamType N+1 loops).
     *
     * @return array{
     *     gradesData: Collection<int, array{grade: Grade, hub_ready: bool, ready_areas: Collection<int, array{area: LearningArea, ready_terms: Collection<int, Term>, hub_ready: bool}>}>,
     *     nationalExams: Collection<int, array{exam: NationalExam, ready: bool}>,
     *     terms: Collection<int, Term>,
     *     allGrades: Collection<int, Grade>,
     * }
     */
    public function buildHomeReadinessData(): array
    {
        $minPapers = (int) SiteSetting::getValue('readiness_min_landing_papers', 1);
        $minIntroWords = (int) SiteSetting::getValue('readiness_min_landing_intro_words', 80);
        $minHubWords = (int) SiteSetting::getValue('readiness_min_hub_intro_words', 25);

        $grades = Grade::query()
            ->active()
            ->ordered()
            ->with(['verifiedLearningAreas'])
            ->get();

        $terms = Term::query()->ordered()->get();
        $examTypes = ExamType::query()->ordered()->get();
        $nationalExams = NationalExam::query()->active()->ordered()->get();
        $activeLearningAreas = LearningArea::query()->active()->orderBy('name')->get()->keyBy('id');

        $termPaperCounts = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_TERM)
            ->whereNotNull('grade_id')
            ->whereNotNull('learning_area_id')
            ->whereNotNull('term_id')
            ->whereNotNull('exam_type_id')
            ->selectRaw('grade_id, learning_area_id, term_id, exam_type_id, COUNT(*) as aggregate')
            ->groupBy('grade_id', 'learning_area_id', 'term_id', 'exam_type_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                "{$row->grade_id}:{$row->learning_area_id}:{$row->term_id}:{$row->exam_type_id}" => (int) $row->aggregate,
            ]);

        $nationalPaperCounts = Paper::query()
            ->verifiedReady()
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->whereNotNull('national_exam_id')
            ->whereNotNull('learning_area_id')
            ->selectRaw('national_exam_id, learning_area_id, COUNT(*) as aggregate')
            ->groupBy('national_exam_id', 'learning_area_id')
            ->get()
            ->groupBy('national_exam_id');

        $contentsByPath = LandingPageContent::query()
            ->where('is_demo', false)
            ->select([
                'id',
                'url_path',
                'status',
                'ai_assisted',
                'reviewed_by',
                'reviewed_at',
                'intro_word_count',
                'is_demo',
            ])
            ->get()
            ->keyBy(fn (LandingPageContent $content) => $this->normalizePath($content->url_path));

        $hasApprovedIntro = function (string $urlPath, int $minWords) use ($contentsByPath): bool {
            if ($minWords <= 0) {
                return true;
            }

            /** @var LandingPageContent|null $content */
            $content = $contentsByPath->get($this->normalizePath($urlPath));

            return $content !== null
                && ! $content->is_demo
                && $content->isApprovedForPublic()
                && $content->intro_word_count >= $minWords;
        };

        $gradesData = $grades->map(function (Grade $grade) use ($terms, $examTypes, $termPaperCounts, $minPapers, $minIntroWords, $minHubWords, $hasApprovedIntro): array {
            $gradePaperTotal = 0;

            $readyAreas = $grade->verifiedLearningAreas->map(function (LearningArea $area) use ($grade, $terms, $examTypes, $termPaperCounts, $minPapers, $minIntroWords, $minHubWords, $hasApprovedIntro, &$gradePaperTotal): ?array {
                $areaPaperTotal = 0;

                $readyTerms = $terms->filter(function (Term $term) use ($grade, $area, $examTypes, $termPaperCounts, $minPapers, $minIntroWords, $hasApprovedIntro, &$areaPaperTotal): bool {
                    $termHasReady = false;

                    foreach ($examTypes as $examType) {
                        $count = $termPaperCounts["{$grade->id}:{$area->id}:{$term->id}:{$examType->id}"] ?? 0;

                        if ($count < $minPapers) {
                            continue;
                        }

                        $urlPath = sprintf('/%s/%s/%s/%s', $grade->slug, $area->slug, $term->slug, $examType->slug);

                        if ($hasApprovedIntro($urlPath, $minIntroWords)) {
                            $areaPaperTotal += $count;
                            $termHasReady = true;
                        }
                    }

                    return $termHasReady;
                })->values();

                if ($readyTerms->isEmpty()) {
                    return null;
                }

                $gradePaperTotal += $areaPaperTotal;

                return [
                    'area' => $area,
                    'ready_terms' => $readyTerms,
                    'paper_count' => $areaPaperTotal,
                    'hub_ready' => $hasApprovedIntro(sprintf('/%s/%s', $grade->slug, $area->slug), $minHubWords),
                ];
            })->filter()->values();

            return [
                'grade' => $grade,
                'hub_ready' => $readyAreas->isNotEmpty() && $hasApprovedIntro('/'.$grade->slug, $minHubWords),
                'paper_count' => $gradePaperTotal,
                'ready_areas' => $readyAreas,
            ];
        });

        $nationalExamsData = $nationalExams->map(function (NationalExam $exam) use ($nationalPaperCounts, $activeLearningAreas, $minPapers, $minIntroWords, $minHubWords, $hasApprovedIntro): array {
            $hasReadySubject = false;

            foreach ($nationalPaperCounts->get($exam->id, collect()) as $row) {
                if ((int) $row->aggregate < $minPapers) {
                    continue;
                }

                $area = $activeLearningAreas->get($row->learning_area_id);

                if (! $area) {
                    continue;
                }

                $urlPath = sprintf('/%s/%s-past-papers', $exam->slug, $area->slug);

                if ($hasApprovedIntro($urlPath, $minIntroWords)) {
                    $hasReadySubject = true;
                    break;
                }
            }

            return [
                'exam' => $exam,
                'ready' => $hasReadySubject && $hasApprovedIntro('/'.$exam->slug, $minHubWords),
            ];
        });

        return [
            'gradesData' => $gradesData,
            'nationalExams' => $nationalExamsData,
            'terms' => $terms,
            'allGrades' => $grades,
        ];
    }

    private function normalizePath(string $urlPath): string
    {
        $normalized = '/'.ltrim(strtolower(trim($urlPath)), '/');

        return $normalized === '/' ? '/' : rtrim($normalized, '/');
    }

    /**
     * @param  array<int, string>  $reasons
     */
    private function checkEditorialIntro(?LandingPageContent $content, int $minWords, array &$reasons): void
    {
        if ($minWords <= 0) {
            return;
        }

        if (! $content) {
            $reasons[] = "Missing approved editorial intro (requires at least {$minWords} words).";

            return;
        }

        if ($content->is_demo) {
            $reasons[] = 'Landing page content is flagged as demo.';

            return;
        }

        if (! $content->isApprovedForPublic()) {
            $reasons[] = $content->ai_assisted && ! $content->reviewed_by
                ? 'AI-assisted intro requires human review and approval.'
                : "Editorial intro status is '{$content->status}' (must be approved).";

            return;
        }

        if ($content->intro_word_count < $minWords) {
            $reasons[] = "Editorial intro has {$content->intro_word_count} words (minimum {$minWords} required).";
        }
    }
}
