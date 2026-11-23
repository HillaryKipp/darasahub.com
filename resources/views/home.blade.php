@extends('layouts.app')

@section('content')
    {{-- Hero & Search (Section 9) --}}
    <section class="bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight max-w-3xl">
                Kenya CBC Past Papers, Term Assessments &amp; National Exams
            </h1>
            <p class="mt-3 text-brand-100 max-w-2xl text-base sm:text-lg">
                Find verified question papers and marking schemes from PP1 to Grade 12, plus KPSEA, KILEA, KJSEA, and KCSE past papers.
            </p>

            <form action="/search" method="GET" class="mt-6 bg-white rounded-xl p-3 shadow-lg grid gap-2 sm:grid-cols-12 text-slate-900">
                <input
                    type="search"
                    name="q"
                    placeholder="Search e.g. Grade 5 Kiswahili Term 2, KCSE Mathematics 2023..."
                    class="sm:col-span-5 px-3 py-2.5 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"
                >
                <select name="grade" class="sm:col-span-3 px-3 py-2.5 rounded-lg border border-slate-200 text-sm bg-white">
                    <option value="">All Grades</option>
                    @foreach($allGrades as $g)
                        <option value="{{ $g->slug }}">{{ $g->name }}</option>
                    @endforeach
                </select>
                <select name="term" class="sm:col-span-2 px-3 py-2.5 rounded-lg border border-slate-200 text-sm bg-white">
                    <option value="">All Terms</option>
                    @foreach($terms as $t)
                        <option value="{{ $t->slug }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="sm:col-span-2 px-4 py-2.5 rounded-lg bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm transition">
                    Search
                </button>
            </form>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">
        {{-- National Exams Quick-Links Grid --}}
        <section aria-labelledby="national-exams-heading">
            <h2 id="national-exams-heading" class="text-xl font-bold text-slate-900 mb-4">National Examinations</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($nationalExams as $item)
                    @php($exam = $item['exam'])
                    <div class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col justify-between">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wide text-brand-700">{{ $exam->curriculum === 'both' ? 'CBC & 8-4-4' : 'CBC' }}</span>
                            <h3 class="mt-1 text-lg font-bold text-slate-900">{{ $exam->name }}</h3>
                            <p class="mt-1 text-xs text-slate-600">{{ $exam->full_name }}</p>
                        </div>
                        <div class="mt-4">
                            @if($item['ready'])
                                <a href="/{{ $exam->slug }}" class="inline-flex items-center text-sm font-semibold text-brand-700 hover:underline">
                                    Browse {{ $exam->name }} &rarr;
                                </a>
                            @else
                                <span class="text-xs text-slate-400">Being verified for publication</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Exams & Assessments Accordion (No-JS <details>/<summary>, links ONLY to ready pages) --}}
        <section aria-labelledby="cbc-grades-heading">
            <div class="flex items-baseline justify-between mb-4">
                <h2 id="cbc-grades-heading" class="text-xl font-bold text-slate-900">CBC Exams &amp; Assessments (PP1 – Grade 12)</h2>
                <span class="text-xs text-slate-500">Only verified, ready sections are linked</span>
            </div>

            @php($readyGrades = $gradesData->filter(fn ($row) => $row['ready_areas']->isNotEmpty()))

            @if($readyGrades->isEmpty())
                <div class="bg-white border border-slate-200 rounded-xl p-6 text-sm text-slate-600">
                    Verified CBC term papers are currently being reviewed by our editorial team. Check back soon or browse <a href="/latest" class="text-brand-700 font-medium hover:underline">latest verified papers</a>.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($readyGrades as $row)
                        @php($grade = $row['grade'])
                        <details class="bg-white border border-slate-200 rounded-xl overflow-hidden group">
                            <summary class="px-5 py-4 flex items-center justify-between cursor-pointer font-semibold text-slate-900 hover:bg-slate-50">
                                <span class="flex items-center gap-3">
                                    <span>{{ $grade->name }}</span>
                                    <span class="text-xs font-normal text-slate-500">({{ $row['ready_areas']->count() }} ready learning {{ \Illuminate\Support\Str::plural('area', $row['ready_areas']->count()) }})</span>
                                </span>
                                <span class="text-slate-400 group-open:rotate-180 transition-transform">&#9662;</span>
                            </summary>
                            <div class="px-5 pb-5 pt-2 border-t border-slate-100">
                                @if($row['hub_ready'])
                                    <p class="mb-3 text-xs">
                                        <a href="/{{ $grade->slug }}" class="font-semibold text-brand-700 hover:underline">View {{ $grade->name }} Hub &rarr;</a>
                                    </p>
                                @endif
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($row['ready_areas'] as $areaRow)
                                        @php($area = $areaRow['area'])
                                        <div class="border border-slate-100 rounded-lg p-3 bg-slate-50/70">
                                            @if($areaRow['hub_ready'])
                                                <a href="/{{ $grade->slug }}/{{ $area->slug }}" class="font-semibold text-sm text-slate-900 hover:text-brand-700 hover:underline">
                                                    {{ $area->name }}
                                                </a>
                                            @else
                                                <span class="font-semibold text-sm text-slate-900">{{ $area->name }}</span>
                                            @endif
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @foreach($areaRow['ready_terms'] as $term)
                                                    <a href="/{{ $grade->slug }}/{{ $area->slug }}/{{ $term->slug }}" class="text-xs px-2 py-0.5 rounded bg-white border border-slate-200 text-brand-700 hover:border-brand-600">
                                                        {{ $term->name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Latest Verified & Most Downloaded Papers --}}
        @if($latestPapers->isNotEmpty() || $popularPapers->isNotEmpty())
            <div class="grid gap-8 lg:grid-cols-2">
                @if($latestPapers->isNotEmpty())
                    <section>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold text-slate-900">Latest Verified Papers</h2>
                            <a href="/latest" class="text-xs font-semibold text-brand-700 hover:underline">View all &rarr;</a>
                        </div>
                        <div class="space-y-3">
                            @foreach($latestPapers as $paper)
                                @include('partials.paper-card', ['paper' => $paper])
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($popularPapers->isNotEmpty())
                    <section>
                        <h2 class="text-lg font-bold text-slate-900 mb-4">Most Downloaded Papers</h2>
                        <div class="space-y-3">
                            @foreach($popularPapers as $paper)
                                @include('partials.paper-card', ['paper' => $paper])
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        @endif

        {{-- Short Editorial Block --}}
        <section class="bg-white border border-slate-200 rounded-xl p-6 text-sm text-slate-600 leading-relaxed space-y-2">
            <h2 class="text-base font-bold text-slate-900">How DarasaHub Organizes CBC &amp; National Past Papers</h2>
            <p>
                Every assessment listed on DarasaHub is verified for curriculum alignment, source attribution, and working access before publication. Term-based school assessments are organized by grade, official KICD learning area, term, and exam type (Opener, Midterm, End Term), while national examinations (KPSEA, KILEA, KJSEA, and KCSE) are organized by exam, subject, and year with clear labels separating CBC and legacy 8-4-4 papers.
            </p>
        </section>
    </div>
@endsection
