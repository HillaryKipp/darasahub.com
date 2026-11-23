<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Services\ReadinessService;
use App\Services\SeoMetaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly ReadinessService $readiness,
        private readonly SeoMetaService $seo,
    ) {}

    public function index(Request $request): View
    {
        $siteName = $this->seo->siteName();

        // Build Exams & Assessments accordion and National Exams data in batched queries (Section 8 & 9)
        $homeReadiness = $this->readiness->buildHomeReadinessData();

        $latestPapers = Paper::query()
            ->verifiedReady()
            ->with(['grade', 'learningArea', 'term', 'examType', 'nationalExam'])
            ->latest('published_at')
            ->limit(12)
            ->get()
            ->filter(fn (Paper $paper) => $this->readiness->evaluatePaper($paper)->ready)
            ->take(8)
            ->values();

        $popularPapers = Paper::query()
            ->verifiedReady()
            ->with(['grade', 'learningArea', 'term', 'examType', 'nationalExam'])
            ->orderByDesc('download_count')
            ->limit(12)
            ->get()
            ->filter(fn (Paper $paper) => $this->readiness->evaluatePaper($paper)->ready)
            ->take(8)
            ->values();

        $canonical = $this->seo->canonicalUrl($request, '/');

        return view('home', [
            'gradesData' => $homeReadiness['gradesData'],
            'nationalExams' => $homeReadiness['nationalExams'],
            'latestPapers' => $latestPapers,
            'popularPapers' => $popularPapers,
            'terms' => $homeReadiness['terms'],
            'allGrades' => $homeReadiness['allGrades'],
            'seo' => [
                'title' => "{$siteName} | Kenya CBC Past Papers, Assessments & National Exams",
                'description' => 'Verified Kenyan CBC past papers, term assessments (PP1 to Grade 12), and national exams (KPSEA, KILEA, KJSEA, KCSE) organized by grade, learning area, and term.',
                'canonical' => $canonical,
                'robots' => 'index, follow',
                'schemas' => [$this->seo->organizationSchema()],
            ],
        ]);
    }

    public function latest(Request $request): View
    {
        $papers = Paper::query()
            ->verifiedReady()
            ->with(['grade', 'learningArea', 'term', 'examType', 'nationalExam'])
            ->latest('published_at')
            ->paginate(20);

        $canonical = $this->seo->canonicalUrl($request, '/latest', $papers->currentPage());
        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => 'Latest Verified Papers', 'url' => null],
        ];

        return view('latest', [
            'papers' => $papers,
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => 'Latest Verified CBC & National Exam Papers | '.$this->seo->siteName(),
                'description' => 'Browse the newest verified Kenyan CBC term exams and national past papers added to '.$this->seo->siteName().'.',
                'canonical' => $canonical,
                'robots' => $papers->total() > 0 ? 'index, follow' : 'noindex, follow',
                'schemas' => [$this->seo->breadcrumbSchema($breadcrumbs)],
            ],
        ]);
    }
}
