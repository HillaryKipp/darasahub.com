<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\LearningArea;
use App\Models\NationalExam;
use App\Models\Paper;
use App\Models\Term;
use App\Services\SeoMetaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        private readonly SeoMetaService $seo,
    ) {}

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $gradeSlug = trim((string) $request->query('grade', ''));
        $subjectSlug = trim((string) $request->query('subject', ''));
        $termSlug = trim((string) $request->query('term', ''));
        $examSlug = trim((string) $request->query('exam', ''));
        $year = $request->filled('year') && ctype_digit((string) $request->query('year'))
            ? (int) $request->query('year')
            : null;
        $curriculum = in_array($request->query('curriculum'), [Paper::CURRICULUM_CBC, Paper::CURRICULUM_LEGACY_844], true)
            ? (string) $request->query('curriculum')
            : '';

        $query = Paper::query()
            ->verifiedReady()
            ->with(['grade', 'learningArea', 'term', 'examType', 'nationalExam']);

        if ($q !== '') {
            $driver = DB::getDriverName();
            if (in_array($driver, ['mysql', 'mariadb'], true) && mb_strlen($q) >= 3) {
                $booleanQuery = collect(preg_split('/\s+/', preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $q) ?: ''))
                    ->filter(fn (string $word) => mb_strlen($word) >= 2)
                    ->map(fn (string $word) => '+'.$word.'*')
                    ->implode(' ');

                if ($booleanQuery !== '') {
                    $query->where(function (Builder $sub) use ($booleanQuery, $q): void {
                        $sub->whereRaw('MATCH(papers.title, papers.description, papers.source_name) AGAINST (? IN BOOLEAN MODE)', [$booleanQuery])
                            ->orWhereHas('grade', fn (Builder $g) => $g->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('learningArea', fn (Builder $a) => $a->where('name', 'like', "%{$q}%"))
                            ->orWhereHas('nationalExam', fn (Builder $e) => $e->where('name', 'like', "%{$q}%"));
                    });
                }
            } else {
                $query->where(function (Builder $sub) use ($q): void {
                    $sub->where('papers.title', 'like', "%{$q}%")
                        ->orWhere('papers.description', 'like', "%{$q}%")
                        ->orWhere('papers.source_name', 'like', "%{$q}%")
                        ->orWhereHas('grade', fn (Builder $g) => $g->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('learningArea', fn (Builder $a) => $a->where('name', 'like', "%{$q}%"))
                        ->orWhereHas('nationalExam', fn (Builder $e) => $e->where('name', 'like', "%{$q}%"));
                });
            }
        }

        if ($gradeSlug !== '') {
            $query->whereHas('grade', fn (Builder $g) => $g->where('slug', $gradeSlug));
        }

        if ($subjectSlug !== '') {
            $query->whereHas('learningArea', fn (Builder $a) => $a->where('slug', $subjectSlug));
        }

        if ($termSlug !== '') {
            $query->whereHas('term', fn (Builder $t) => $t->where('slug', $termSlug));
        }

        if ($examSlug !== '') {
            $query->whereHas('nationalExam', fn (Builder $e) => $e->where('slug', $examSlug));
        }

        if ($year !== null) {
            $query->where('papers.year', $year);
        }

        if ($curriculum !== '') {
            $query->where('papers.curriculum', $curriculum);
        }

        $papers = $query->orderByDesc('year')->orderByDesc('download_count')->paginate(20)->withQueryString();

        $activeFilters = array_filter([
            'q' => $q !== '' ? "Keyword: {$q}" : null,
            'grade' => $gradeSlug !== '' ? 'Grade: '.(Grade::query()->where('slug', $gradeSlug)->value('name') ?? $gradeSlug) : null,
            'subject' => $subjectSlug !== '' ? 'Subject: '.(LearningArea::query()->where('slug', $subjectSlug)->value('name') ?? $subjectSlug) : null,
            'term' => $termSlug !== '' ? 'Term: '.(Term::query()->where('slug', $termSlug)->value('name') ?? $termSlug) : null,
            'exam' => $examSlug !== '' ? 'Exam: '.(NationalExam::query()->where('slug', $examSlug)->value('name') ?? $examSlug) : null,
            'year' => $year !== null ? "Year: {$year}" : null,
            'curriculum' => $curriculum !== '' ? 'Curriculum: '.($curriculum === Paper::CURRICULUM_LEGACY_844 ? '8-4-4 Legacy' : 'CBC') : null,
        ]);

        $breadcrumbs = [
            ['label' => 'Home', 'url' => $this->seo->canonicalUrl($request, '/')],
            ['label' => 'Search Papers', 'url' => null],
        ];

        return view('search', [
            'papers' => $papers,
            'filters' => [
                'q' => $q,
                'grade' => $gradeSlug,
                'subject' => $subjectSlug,
                'term' => $termSlug,
                'exam' => $examSlug,
                'year' => $year,
                'curriculum' => $curriculum,
            ],
            'activeFilters' => $activeFilters,
            'grades' => Grade::query()->active()->ordered()->get(),
            'subjects' => LearningArea::query()->active()->orderBy('name')->get(),
            'terms' => Term::query()->ordered()->get(),
            'nationalExams' => NationalExam::query()->active()->ordered()->get(),
            'breadcrumbs' => $breadcrumbs,
            'seo' => [
                'title' => ($q !== '' ? "Search: {$q} | " : 'Search CBC & National Exam Papers | ').$this->seo->siteName(),
                'description' => 'Search verified Kenyan CBC term exams and national past papers by grade, learning area, term, year, and source.',
                'canonical' => $this->seo->canonicalUrl($request, '/search'),
                // Search results are NEVER indexed (Section 14)
                'robots' => 'noindex, follow',
                'schemas' => [$this->seo->breadcrumbSchema($breadcrumbs)],
            ],
        ]);
    }
}
