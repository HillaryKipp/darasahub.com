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

        {{-- Exams & Assessments Nested Dropdown Accordion (EasyTVET-style, No-JS <details>/<summary>, Alpine.js enhancement) --}}
        <section
            aria-labelledby="cbc-grades-heading"
            x-data="{
                filter: '',
                expandAll() {
                    this.$refs.accordion.querySelectorAll('details.grade-accordion').forEach(d => d.open = true);
                },
                collapseAll() {
                    this.$refs.accordion.querySelectorAll('details').forEach(d => d.open = false);
                }
            }"
        >
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div>
                    <h2 id="cbc-grades-heading" class="text-xl sm:text-2xl font-extrabold text-slate-900">
                        CBC Exams &amp; Assessments (PP1 – Grade 12)
                    </h2>
                    <p class="mt-1 text-xs sm:text-sm text-slate-500">
                        Select a grade below to expand learning areas and term exams.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <input
                        type="search"
                        x-model="filter"
                        placeholder="Quick filter grade or subject..."
                        class="px-3 py-1.5 text-xs sm:text-sm rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-brand-600"
                    >
                    <button type="button" @click="expandAll()" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200 bg-white text-slate-700 hover:border-brand-600 hover:text-brand-700">
                        Expand All
                    </button>
                    <button type="button" @click="collapseAll()" class="px-2.5 py-1.5 text-xs font-medium rounded-lg border border-slate-200 bg-white text-slate-700 hover:border-brand-600 hover:text-brand-700">
                        Collapse All
                    </button>
                </div>
            </div>

            @php($readyGrades = $gradesData->filter(fn ($row) => $row['ready_areas']->isNotEmpty()))

            @if($readyGrades->isEmpty())
                <div class="bg-white border border-slate-200 rounded-xl p-6 text-sm text-slate-600">
                    Verified CBC term papers are currently being reviewed by our editorial team. Check back soon or browse <a href="/latest" class="text-brand-700 font-medium hover:underline">latest verified papers</a>.
                </div>
            @else
                <div x-ref="accordion" class="space-y-3">
                    @foreach($readyGrades as $row)
                        @php($grade = $row['grade'])
                        @php($levelLabel = \App\Models\Grade::LEVELS[$grade->level] ?? '')
                        @php($searchIndex = strtolower($grade->name.' '.$levelLabel.' '.$row['ready_areas']->pluck('area.name')->implode(' ')))

                        <details
                            class="grade-accordion bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden group/grade"
                            x-show="filter.trim() === '' || {{ json_encode($searchIndex) }}.includes(filter.trim().toLowerCase())"
                            @if($loop->first) open @endif
                        >
                            {{-- Level 1: Grade Dropdown Header --}}
                            <summary class="px-5 py-4 flex items-center justify-between cursor-pointer select-none bg-white hover:bg-brand-50/50 transition border-l-4 border-brand-700">
                                <div class="flex flex-wrap items-center gap-2.5">
                                    <span class="text-base font-bold text-slate-900">{{ $grade->name }}</span>
                                    @if($levelLabel)
                                        <span class="px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide rounded-full bg-brand-50 text-brand-800">
                                            {{ $levelLabel }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-slate-500">
                                        {{ $row['ready_areas']->count() }} {{ \Illuminate\Support\Str::plural('Learning Area', $row['ready_areas']->count()) }}
                                        &middot;
                                        {{ $row['paper_count'] }} {{ \Illuminate\Support\Str::plural('Paper', $row['paper_count']) }}
                                    </span>
                                </div>
                                <span class="ml-3 inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-600 group-open/grade:bg-brand-700 group-open/grade:text-white transition">
                                    <svg class="w-4 h-4 transition-transform duration-200 group-open/grade:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </summary>

                            {{-- Level 2: Nested Learning Areas Dropdowns --}}
                            <div class="px-4 sm:px-5 pb-4 pt-3 bg-slate-50/70 border-t border-slate-200 space-y-2">
                                @if($row['hub_ready'])
                                    <div class="flex items-center justify-between pb-2 mb-1 border-b border-slate-200/80 text-xs">
                                        <span class="text-slate-500">Click any learning area below to view available terms</span>
                                        <a href="/{{ $grade->slug }}" class="font-semibold text-brand-700 hover:underline">
                                            Open {{ $grade->name }} Hub &rarr;
                                        </a>
                                    </div>
                                @endif

                                @foreach($row['ready_areas'] as $areaRow)
                                    @php($area = $areaRow['area'])
                                    <details class="bg-white border border-slate-200 rounded-lg overflow-hidden group/area">
                                        <summary class="px-4 py-3 flex items-center justify-between cursor-pointer select-none hover:bg-slate-50 transition">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-semibold text-sm text-slate-800">{{ $area->name }}</span>
                                                <span class="px-2 py-0.5 rounded bg-slate-100 text-[11px] font-medium text-slate-600">
                                                    {{ $areaRow['paper_count'] }} {{ \Illuminate\Support\Str::plural('paper', $areaRow['paper_count']) }}
                                                </span>
                                            </div>
                                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200 group-open/area:rotate-180 group-open/area:text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </summary>

                                        {{-- Level 3: Ready Terms & Hub Links --}}
                                        <div class="px-4 py-3 bg-brand-50/30 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @foreach($areaRow['ready_terms'] as $term)
                                                    <a
                                                        href="/{{ $grade->slug }}/{{ $area->slug }}/{{ $term->slug }}"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-white border border-brand-600/30 text-xs font-semibold text-brand-800 hover:bg-brand-700 hover:text-white hover:border-brand-700 transition shadow-xs"
                                                    >
                                                        <span>{{ $term->name }} Exams</span>
                                                        <span aria-hidden="true">&rarr;</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                            @if($areaRow['hub_ready'])
                                                <a href="/{{ $grade->slug }}/{{ $area->slug }}" class="text-xs font-medium text-slate-600 hover:text-brand-700 hover:underline">
                                                    All {{ $area->name }} &rarr;
                                                </a>
                                            @endif
                                        </div>
                                    </details>
                                @endforeach
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
