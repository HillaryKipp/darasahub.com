<?php

namespace App\Http\Controllers;

use App\Models\ExamType;
use App\Models\Grade;
use App\Models\LearningArea;
use App\Models\Paper;
use App\Models\Term;
use App\Services\InternalLinkingService;
use App\Services\ReadinessService;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TermDirectoryController extends Controller
{
    public function __construct(
        private readonly ReadinessService $readiness,
        private readonly SeoMetaService $seo,
        private readonly InternalLinkingService $internalLinks,
    ) {}

    public function gradeHub(Request $request, string $gradeSlug): View
    {
        $grade = Grade::query()->active()->where('slug', $gradeSlug)->firstOrFail();
        $content = $this->readiness->findContent('/'.$grade->slug);
        $evaluation = $this->readiness->evaluateGradeHub($grade, $content);
        $readyAreas = $this->readiness->getReadyLearningAreasForGrade($grade);

        $canonical = $this->seo->canonicalUrl($request, '/'.$grade->slug);
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $grade->name, 'url' => null],
        ];

        $h1 = $content?->h1 ?: "{$grade->name} CBC Exams & Past Papers";
        $title = $content?->meta_title ?: "{$grade->name} CBC Past Papers & Term Exams | {$this->seo->siteName()}";
        $description = $content?->meta_description ?: "Browse verified {$grade->name} CBC past papers and assessments organized by learning area, term, and exam type.";

        $schemas = [$this->seo->breadcrumbSchema($breadcrumbs)];
        if ($faq = $this->seo->faqSchema($content)) {
            $schemas[] = $faq;
        }

        return view('directory.grade-hub', [
            'grade' => $grade,
            'content' => $content,
            'readyAreas' => $readyAreas,
            'readiness' => $evaluation,
            'breadcrumbs' => $breadcrumbs,
            'h1' => $h1,
            'seo' => [
                'title' => $title,
                'description' => $description,
                'canonical' => $canonical,
                'robots' => $evaluation->robotsMeta(),
                'og_image' => $content?->og_image_path,
                'schemas' => $schemas,
            ],
        ]);
    }

    public function learningAreaHub(Request $request, string $gradeSlug, string $areaSlug): View
    {
        $grade = Grade::query()->active()->where('slug', $gradeSlug)->firstOrFail();
        $area = $grade->verifiedLearningAreas()->where('learning_areas.slug', $areaSlug)->firstOrFail();

        $path = "/{$grade->slug}/{$area->slug}";
        $content = $this->readiness->findContent($path);
        $evaluation = $this->readiness->evaluateLearningAreaHub($grade, $area, $content);

        $termsData = Term::query()->ordered()->get()->map(fn (Term $term) => [
            'term' => $term,
            'term_hub_ready' => $this->readiness->evaluateTermHub($grade, $area, $term)->ready,
            'ready_exam_types' => $this->readiness->getReadyExamTypesForTerm($grade, $area, $term),
        ])->filter(fn (array $row) => $row['ready_exam_types']->isNotEmpty())->values();

        $canonical = $this->seo->canonicalUrl($request, $path);
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $grade->name, 'url' => $this->seo->canonicalUrl($request, '/'.$grade->slug)],
            ['label' => $area->name, 'url' => null],
        ];

        $h1 = $content?->h1 ?: "{$grade->name} {$area->name} Past Papers";
        $title = $content?->meta_title ?: "{$grade->name} {$area->name} Exams & Past Papers | {$this->seo->siteName()}";
        $description = $content?->meta_description ?: "Verified {$grade->name} {$area->name} Term 1, Term 2, and Term 3 exams and marking schemes for Kenyan CBC learners.";

        $schemas = [$this->seo->breadcrumbSchema($breadcrumbs)];
        if ($faq = $this->seo->faqSchema($content)) {
            $schemas[] = $faq;
        }

        return view('directory.learning-area-hub', [
            'grade' => $grade,
            'area' => $area,
            'content' => $content,
            'termsData' => $termsData,
            'readiness' => $evaluation,
            'breadcrumbs' => $breadcrumbs,
            'h1' => $h1,
            'seo' => [
                'title' => $title,
                'description' => $description,
                'canonical' => $canonical,
                'robots' => $evaluation->robotsMeta(),
                'og_image' => $content?->og_image_path,
                'schemas' => $schemas,
            ],
        ]);
    }

    public function termHub(Request $request, string $gradeSlug, string $areaSlug, string $termSlug): View
    {
        $grade = Grade::query()->active()->where('slug', $gradeSlug)->firstOrFail();
        $area = $grade->verifiedLearningAreas()->where('learning_areas.slug', $areaSlug)->firstOrFail();
        $term = Term::query()->where('slug', $termSlug)->firstOrFail();

        $path = "/{$grade->slug}/{$area->slug}/{$term->slug}";
        $content = $this->readiness->findContent($path);
        $evaluation = $this->readiness->evaluateTermHub($grade, $area, $term, $content);
        $readyExamTypes = $this->readiness->getReadyExamTypesForTerm($grade, $area, $term);

        $canonical = $this->seo->canonicalUrl($request, $path);
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $grade->name, 'url' => $this->seo->canonicalUrl($request, '/'.$grade->slug)],
            ['label' => $area->name, 'url' => $this->seo->canonicalUrl($request, "/{$grade->slug}/{$area->slug}")],
            ['label' => $term->name, 'url' => null],
        ];

        $h1 = $content?->h1 ?: "{$grade->name} {$area->name} {$term->name} Exams";
        $title = $content?->meta_title ?: "{$grade->name} {$area->name} {$term->name} Past Papers | {$this->seo->siteName()}";
        $description = $content?->meta_description ?: "Access verified {$grade->name} {$area->name} {$term->name} Opener, Midterm, and End Term exams.";

        $schemas = [$this->seo->breadcrumbSchema($breadcrumbs)];
        if ($faq = $this->seo->faqSchema($content)) {
            $schemas[] = $faq;
        }

        return view('directory.term-hub', [
            'grade' => $grade,
            'area' => $area,
            'term' => $term,
            'content' => $content,
            'readyExamTypes' => $readyExamTypes,
            'readiness' => $evaluation,
            'breadcrumbs' => $breadcrumbs,
            'h1' => $h1,
            'seo' => [
                'title' => $title,
                'description' => $description,
                'canonical' => $canonical,
                'robots' => $evaluation->robotsMeta(),
                'og_image' => $content?->og_image_path,
                'schemas' => $schemas,
            ],
        ]);
    }

    public function termLanding(
        Request $request,
        string $gradeSlug,
        string $areaSlug,
        string $termSlug,
        string $examTypeSlug
    ): View {
        $grade = Grade::query()->active()->where('slug', $gradeSlug)->firstOrFail();
        $area = $grade->verifiedLearningAreas()->where('learning_areas.slug', $areaSlug)->firstOrFail();
        $term = Term::query()->where('slug', $termSlug)->firstOrFail();
        $examType = ExamType::query()->where('slug', $examTypeSlug)->firstOrFail();

        $path = "/{$grade->slug}/{$area->slug}/{$term->slug}/{$examType->slug}";
        $content = $this->readiness->findContent($path);
        $evaluation = $this->readiness->evaluateTermLanding($grade, $area, $term, $examType, $content);

        $papers = Paper::query()
            ->verifiedReady()
            ->with(['grade', 'learningArea', 'term', 'examType', 'relatedPaper'])
            ->where('category', Paper::CATEGORY_TERM)
            ->where('grade_id', $grade->id)
            ->where('learning_area_id', $area->id)
            ->where('term_id', $term->id)
            ->where('exam_type_id', $examType->id)
            ->orderByDesc('year')
            ->orderBy('title')
            ->paginate(20);

        $latestYear = $papers->first()?->year;
        $yearTag = $latestYear ? " ({$latestYear})" : '';

        $canonical = $this->seo->canonicalUrl($request, $path, $papers->currentPage());
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $grade->name, 'url' => $this->seo->canonicalUrl($request, '/'.$grade->slug)],
            ['label' => $area->name, 'url' => $this->seo->canonicalUrl($request, "/{$grade->slug}/{$area->slug}")],
            ['label' => $term->name, 'url' => $this->seo->canonicalUrl($request, "/{$grade->slug}/{$area->slug}/{$term->slug}")],
            ['label' => $examType->name, 'url' => null],
        ];

        $h1 = $content?->h1 ?: "{$grade->name} {$area->name} {$term->name} {$examType->name}";
        $title = $content?->meta_title ?: "{$grade->name} {$area->name} {$term->name} {$examType->name}{$yearTag} | Free CBC Past Papers";
        $description = $content?->meta_description ?: "Download verified {$grade->name} {$area->name} {$term->name} {$examType->name} and marking schemes on {$this->seo->siteName()}.";

        $itemList = $papers->getCollection()->map(fn (Paper $p) => [
            'name' => $p->title,
            'url' => $this->seo->canonicalUrl($request, $p->url_path),
        ])->all();

        $schemas = [
            $this->seo->breadcrumbSchema($breadcrumbs),
            $this->seo->collectionPageSchema($h1, $description, $canonical, $itemList),
        ];
        if ($faq = $this->seo->faqSchema($content)) {
            $schemas[] = $faq;
        }

        return view('directory.term-landing', [
            'grade' => $grade,
            'area' => $area,
            'term' => $term,
            'examType' => $examType,
            'content' => $content,
            'papers' => $papers,
            'internalLinks' => $this->internalLinks->forTermLanding($grade, $area, $term, $examType),
            'readiness' => $evaluation,
            'breadcrumbs' => $breadcrumbs,
            'h1' => $h1,
            'seo' => [
                'title' => $title,
                'description' => $description,
                'canonical' => $canonical,
                'robots' => $evaluation->robotsMeta(),
                'og_image' => $content?->og_image_path,
                'schemas' => $schemas,
            ],
        ]);
    }

    public function paperDetail(
        Request $request,
        string $gradeSlug,
        string $areaSlug,
        string $termSlug,
        string $examTypeSlug,
        string $paperSlug
    ): View {
        $grade = Grade::query()->active()->where('slug', $gradeSlug)->firstOrFail();
        $area = LearningArea::query()->active()->where('slug', $areaSlug)->firstOrFail();
        $term = Term::query()->where('slug', $termSlug)->firstOrFail();
        $examType = ExamType::query()->where('slug', $examTypeSlug)->firstOrFail();

        // Draft, archived, demo, or non-permitted papers return 404 (Section 4 & 8)
        $paper = Paper::query()
            ->publishedPublic()
            ->with(['grade', 'learningArea', 'term', 'examType', 'relatedPaper'])
            ->where('category', Paper::CATEGORY_TERM)
            ->where('grade_id', $grade->id)
            ->where('learning_area_id', $area->id)
            ->where('term_id', $term->id)
            ->where('exam_type_id', $examType->id)
            ->where('slug', $paperSlug)
            ->firstOrFail();

        $evaluation = $this->readiness->evaluatePaper($paper);

        $relatedPapers = Paper::query()
            ->verifiedReady()
            ->with(['grade', 'learningArea', 'term', 'examType'])
            ->where('category', Paper::CATEGORY_TERM)
            ->where('grade_id', $grade->id)
            ->where('learning_area_id', $area->id)
            ->where('term_id', $term->id)
            ->whereKeyNot($paper->id)
            ->orderByDesc('year')
            ->limit(6)
            ->get()
            ->filter(fn (Paper $p) => $this->readiness->evaluatePaper($p)->ready)
            ->values();

        $canonical = $this->seo->canonicalUrl($request, $paper->url_path);
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $grade->name, 'url' => $this->seo->canonicalUrl($request, '/'.$grade->slug)],
            ['label' => $area->name, 'url' => $this->seo->canonicalUrl($request, "/{$grade->slug}/{$area->slug}")],
            ['label' => $term->name, 'url' => $this->seo->canonicalUrl($request, "/{$grade->slug}/{$area->slug}/{$term->slug}")],
            ['label' => $examType->name, 'url' => $this->seo->canonicalUrl($request, "/{$grade->slug}/{$area->slug}/{$term->slug}/{$examType->slug}")],
            ['label' => $paper->title, 'url' => null],
        ];

        $schemas = [
            $this->seo->breadcrumbSchema($breadcrumbs),
            $this->seo->learningResourceSchema($paper, $canonical),
        ];

        return view('directory.paper-detail', [
            'paper' => $paper,
            'relatedPapers' => $relatedPapers,
            'readiness' => $evaluation,
            'breadcrumbs' => $breadcrumbs,
            'parentLandingUrl' => "/{$grade->slug}/{$area->slug}/{$term->slug}/{$examType->slug}",
            'parentLandingLabel' => "{$grade->name} {$area->name} {$term->name} {$examType->name}",
            'seo' => [
                'title' => "{$paper->title} | {$this->seo->siteName()}",
                'description' => str(strip_tags((string) $paper->description))->limit(155)->value() ?: "{$paper->title} for {$grade->name} {$area->name} ({$term->name} {$examType->name}).",
                'canonical' => $canonical,
                'robots' => $evaluation->robotsMeta(),
                'og_image' => $paper->og_image_path,
                'schemas' => $schemas,
            ],
        ]);
    }
}
