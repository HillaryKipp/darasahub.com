@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-5 text-center">Search Verified Exam Papers</h1>

    <form action="/search" method="GET" class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="sm:col-span-2 lg:col-span-4">
            <label for="q" class="sr-only">Keyword</label>
            <input
                id="q"
                type="search"
                name="q"
                value="{{ $filters['q'] }}"
                placeholder="Search title, grade, subject, year, or source..."
                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"
            >
        </div>

        <select name="grade" class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
            <option value="">All Grades</option>
            @foreach($grades as $grade)
                <option value="{{ $grade->slug }}" @selected($filters['grade'] === $grade->slug)>{{ $grade->name }}</option>
            @endforeach
        </select>

        <select name="subject" class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
            <option value="">All Learning Areas</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->slug }}" @selected($filters['subject'] === $subject->slug)>{{ $subject->name }}</option>
            @endforeach
        </select>

        <select name="term" class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
            <option value="">All Terms</option>
            @foreach($terms as $term)
                <option value="{{ $term->slug }}" @selected($filters['term'] === $term->slug)>{{ $term->name }}</option>
            @endforeach
        </select>

        <select name="exam" class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
            <option value="">All National Exams</option>
            @foreach($nationalExams as $exam)
                <option value="{{ $exam->slug }}" @selected($filters['exam'] === $exam->slug)>{{ $exam->name }}</option>
            @endforeach
        </select>

        <input
            type="number"
            name="year"
            min="1990"
            max="2035"
            value="{{ $filters['year'] }}"
            placeholder="Year (e.g. 2024)"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
        >

        <select name="curriculum" class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
            <option value="">All Curricula</option>
            <option value="cbc" @selected($filters['curriculum'] === 'cbc')>CBC</option>
            <option value="legacy_844" @selected($filters['curriculum'] === 'legacy_844')>8-4-4 Legacy</option>
        </select>

        <div class="sm:col-span-2 flex items-center justify-center sm:justify-start gap-2">
            <button type="submit" class="px-5 py-2 rounded-lg bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm transition">
                Apply Filters
            </button>
            @if(!empty($activeFilters))
                <a href="/search" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-medium">
                    Clear All
                </a>
            @endif
        </div>
    </form>

    {{-- Active Filter Chips --}}
    @if(!empty($activeFilters))
        <div class="flex flex-wrap items-center justify-center gap-2 mb-6">
            @foreach($activeFilters as $key => $label)
                @php($without = collect(request()->query())->except([$key, 'page'])->all())
                <a
                    href="{{ '/search'.($without ? '?'.http_build_query($without) : '') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-brand-50 border border-brand-100 text-xs font-medium text-brand-800 hover:bg-brand-100"
                >
                    <span>{{ $label }}</span>
                    <span aria-hidden="true">&times;</span>
                </a>
            @endforeach
        </div>
    @endif

    <p class="text-sm text-slate-500 mb-4 text-center">
        Showing {{ $papers->total() }} verified {{ \Illuminate\Support\Str::plural('paper', $papers->total()) }}
    </p>

    @if($papers->isEmpty())
        <div class="bg-white border border-slate-200 rounded-xl p-6 text-sm text-slate-600 text-center">
            No verified papers matched your current search criteria. Try broadening your filters or browsing by grade on the <a href="/" class="text-brand-700 font-semibold hover:underline">homepage</a>.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($papers as $paper)
                @include('partials.paper-card', ['paper' => $paper])
            @endforeach
        </div>
        <div class="mt-8 flex justify-center">
            {{ $papers->links() }}
        </div>
    @endif
</div>
@endsection
