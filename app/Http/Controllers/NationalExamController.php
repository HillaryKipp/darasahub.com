<?php

namespace App\Http\Controllers;

use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Services\InternalLinkingService;
use App\Services\ReadinessService;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NationalExamController extends Controller
{
    public function __construct(
        private readonly ReadinessService $readiness,
        private readonly SeoMetaService $seo,
        private readonly InternalLinkingService $internalLinks,
    ) {}

    public function examHub(Request $request, string $examSlug): View
    {
        $exam = NationalExam::query()->active()->with('grade')->where('slug', $examSlug)->firstOrFail();
        $content = $this->readiness->findContent('/'.$exam->slug);
        $evaluation = $this->readiness->evaluateNationalHub($exam, $content);
        $readySubjects = $this->readiness->getReadySubjectsForNationalExam($exam);

        $canonical = $this->seo->canonicalUrl($request, '/'.$exam->slug);
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $exam->name, 'url' => null],
        ];

        $h1 = $content?->h1 ?: "{$exam->name} Past Papers ({$exam->full_name})";
        $title = $content?->meta_title ?: "{$exam->name} Past Papers & Marking Schemes | {$this->seo->siteName()}";
        $description = $content?->meta_description ?: "Browse verified {$exam->name} ({$exam->full_name}) past papers organized by subject and year.";

        $schemas = [$this->seo->breadcrumbSchema($breadcrumbs)];
        if ($faq = $this->seo->faqSchema($content)) {
            $schemas[] = $faq;
        }

        return view('national.exam-hub', [
            'exam' => $exam,
            'content' => $content,
            'readySubjects' => $readySubjects,
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

    public function subjectLanding(Request $request, string $examSlug, string $areaSlug): View
    {
        $exam = NationalExam::query()->active()->where('slug', $examSlug)->firstOrFail();
        $area = LearningArea::query()->active()->where('slug', $areaSlug)->firstOrFail();

        $path = "/{$exam->slug}/{$area->slug}-past-papers";
        $content = $this->readiness->findContent($path);
        $evaluation = $this->readiness->evaluateNationalSubject($exam, $area, $content);
        $readyYears = $this->readiness->getReadyYearsForNationalSubject($exam, $area);

        $papers = Paper::query()
            ->verifiedReady()
            ->with(['nationalExam', 'learningArea', 'relatedPaper'])
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->where('national_exam_id', $exam->id)
            ->where('learning_area_id', $area->id)
            ->orderByDesc('year')
            ->orderBy('paper_number')
            ->paginate(20);

        $canonical = $this->seo->canonicalUrl($request, $path, $papers->currentPage());
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $exam->name, 'url' => $this->seo->canonicalUrl($request, '/'.$exam->slug)],
            ['label' => "{$area->name} Past Papers", 'url' => null],
        ];

        $h1 = $content?->h1 ?: "{$exam->name} {$area->name} Past Papers";
        $title = $content?->meta_title ?: "{$exam->name} {$area->name} Past Papers & Marking Schemes | {$this->seo->siteName()}";
        $description = $content?->meta_description ?: "Download verified {$exam->name} {$area->name} past papers and marking schemes across available years.";

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

        return view('national.subject-landing', [
            'exam' => $exam,
            'area' => $area,
            'content' => $content,
            'readyYears' => $readyYears,
            'papers' => $papers,
            'internalLinks' => $this->internalLinks->forNationalPage($exam, $area),
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

    public function yearLanding(Request $request, string $examSlug, string $areaSlug, int $year): View
    {
        $exam = NationalExam::query()->active()->where('slug', $examSlug)->firstOrFail();
        $area = LearningArea::query()->active()->where('slug', $areaSlug)->firstOrFail();

        $path = "/{$exam->slug}/{$area->slug}-past-papers/{$year}";
        $content = $this->readiness->findContent($path);
        $evaluation = $this->readiness->evaluateNationalYear($exam, $area, $year, $content);

        $papers = Paper::query()
            ->verifiedReady()
            ->with(['nationalExam', 'learningArea', 'relatedPaper'])
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->where('national_exam_id', $exam->id)
            ->where('learning_area_id', $area->id)
            ->where('year', $year)
            ->orderBy('paper_number')
            ->paginate(20);

        $readyYears = $this->readiness->getReadyYearsForNationalSubject($exam, $area);
        $adjacentYears = $readyYears->filter(fn (int $y) => $y !== $year)->values();

        $canonical = $this->seo->canonicalUrl($request, $path, $papers->currentPage());
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $exam->name, 'url' => $this->seo->canonicalUrl($request, '/'.$exam->slug)],
            ['label' => "{$area->name} Past Papers", 'url' => $this->seo->canonicalUrl($request, "/{$exam->slug}/{$area->slug}-past-papers")],
            ['label' => (string) $year, 'url' => null],
        ];

        $h1 = $content?->h1 ?: "{$exam->name} {$area->name} Past Papers {$year}";
        $title = $content?->meta_title ?: "{$exam->name} {$area->name} Past Papers {$year} | {$this->seo->siteName()}";
        $description = $content?->meta_description ?: "Verified {$year} {$exam->name} {$area->name} question papers and marking schemes.";

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

        return view('national.year-landing', [
            'exam' => $exam,
            'area' => $area,
            'year' => $year,
            'content' => $content,
            'papers' => $papers,
            'adjacentYears' => $adjacentYears,
            'internalLinks' => $this->internalLinks->forNationalPage($exam, $area, $year),
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

    public function paperDetail(Request $request, string $examSlug, string $areaSlug, int $year, string $paperSlug): View
    {
        $exam = NationalExam::query()->active()->where('slug', $examSlug)->firstOrFail();
        $area = LearningArea::query()->active()->where('slug', $areaSlug)->firstOrFail();

        $paper = Paper::query()
            ->publishedPublic()
            ->with(['nationalExam', 'learningArea', 'grade', 'relatedPaper'])
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->where('national_exam_id', $exam->id)
            ->where('learning_area_id', $area->id)
            ->where('year', $year)
            ->where('slug', $paperSlug)
            ->firstOrFail();

        $evaluation = $this->readiness->evaluatePaper($paper);

        $relatedPapers = Paper::query()
            ->verifiedReady()
            ->with(['nationalExam', 'learningArea'])
            ->where('category', Paper::CATEGORY_NATIONAL)
            ->where('national_exam_id', $exam->id)
            ->where('learning_area_id', $area->id)
            ->whereKeyNot($paper->id)
            ->orderByDesc('year')
            ->limit(6)
            ->get()
            ->filter(fn (Paper $p) => $this->readiness->evaluatePaper($p)->ready)
            ->values();

        $canonical = $this->seo->canonicalUrl($request, $paper->url_path);
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => $exam->name, 'url' => $this->seo->canonicalUrl($request, '/'.$exam->slug)],
            ['label' => "{$area->name} Past Papers", 'url' => $this->seo->canonicalUrl($request, "/{$exam->slug}/{$area->slug}-past-papers")],
            ['label' => (string) $year, 'url' => $this->seo->canonicalUrl($request, "/{$exam->slug}/{$area->slug}-past-papers/{$year}")],
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
            'parentLandingUrl' => "/{$exam->slug}/{$area->slug}-past-papers/{$year}",
            'parentLandingLabel' => "{$exam->name} {$area->name} Past Papers {$year}",
            'seo' => [
                'title' => "{$paper->title} | {$this->seo->siteName()}",
                'description' => str(strip_tags((string) $paper->description))->limit(155)->value() ?: "{$paper->title} ({$exam->name} {$year}).",
                'canonical' => $canonical,
                'robots' => $evaluation->robotsMeta(),
                'og_image' => $paper->og_image_path,
                'schemas' => $schemas,
            ],
        ]);
    }
}
