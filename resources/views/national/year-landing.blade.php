@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <header class="mb-6 text-center">
        <div class="flex flex-wrap items-center justify-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700 mb-2">
            <span>{{ $exam->name }}</span>
            <span class="text-slate-300">/</span>
            <span>{{ $area->name }}</span>
            <span class="text-slate-300">/</span>
            <span>{{ $year }}</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $h1 }}</h1>
    </header>

    {{-- Concise Intro Paragraph --}}
    @if(!empty($content) && $content->isApprovedForPublic() && !empty(trim((string) $content->intro)))
        <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 mb-6 text-sm sm:text-base text-slate-700 leading-relaxed space-y-3 text-center">
            {!! nl2br(e($content->intro)) !!}
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 mb-6 text-sm sm:text-base text-slate-700 leading-relaxed text-center">
            <p class="max-w-3xl mx-auto">
                All verified <strong>{{ $exam->name }} {{ $area->name }} {{ $year }}</strong> question papers and marking schemes in one place. Click any paper title to open it in a new tab, read it in the preview below, or click <em>Download</em>.
            </p>
        </div>
    @endif

    {{-- Year Buttons Bar --}}
    @if(!empty($readyYears) && $readyYears->isNotEmpty())
        <section aria-labelledby="years-bar-heading" class="bg-white border border-slate-200 rounded-xl p-5 mb-8 text-center">
            <div class="flex flex-col items-center gap-1.5 mb-3">
                <h2 id="years-bar-heading" class="text-sm font-bold text-slate-900">
                    {{ $exam->name }} {{ $area->name }} Years
                </h2>
                <a href="/{{ $exam->slug }}/{{ $area->slug }}-past-papers" class="text-xs font-semibold text-brand-700 hover:underline">
                    All {{ $area->name }} years &rarr;
                </a>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-2.5">
                @foreach($readyYears as $yr)
                    @include('partials.status-check-button', [
                        'label' => (string) $yr,
                        'checked' => true,
                        'active' => $yr === $year,
                        'count' => (int) ($yearCounts[$yr] ?? 0) ?: null,
                        'href' => "/{$exam->slug}/{$area->slug}-past-papers/{$yr}",
                    ])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Bundled Year Papers (with previews open directly on this page) --}}
    @if($papers->isNotEmpty())
        <section aria-labelledby="year-papers-heading">
            <h2 id="year-papers-heading" class="text-lg sm:text-xl font-bold text-slate-900 mb-4 text-center">
                {{ $year }} Papers &amp; Marking Schemes
                <span class="text-sm font-medium text-slate-500">({{ $papers->total() }})</span>
            </h2>

            @include('partials.paper-paragraph-list', [
                'papers' => $papers->getCollection(),
                'previewOpen' => true,
                'showDescription' => false,
            ])

            @if($papers->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $papers->links() }}
                </div>
            @endif
        </section>
    @else
        <div class="bg-white border border-slate-200 rounded-xl p-6 text-sm text-slate-600 text-center">
            No verified papers are published for {{ $exam->name }} {{ $area->name }} {{ $year }} yet.
        </div>
    @endif

    {{-- Exam Subjects Grid (matching CBC Learning Areas grid) --}}
    @if(!empty($readySubjects) && $readySubjects->isNotEmpty())
        <section aria-labelledby="exam-subjects-heading" class="mt-10">
            <h2 id="exam-subjects-heading" class="text-lg font-bold text-slate-900 mb-4 text-center">
                {{ $exam->name }} Subjects
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($readySubjects as $subject)
                    @php($isCurrentSubject = $subject->id === $area->id)
                    <a
                        href="/{{ $exam->slug }}/{{ $subject->slug }}-past-papers"
                        class="rounded-xl p-5 border transition flex flex-col items-center text-center {{ $isCurrentSubject ? 'bg-brand-50/40 border-brand-600/60' : 'bg-white border-slate-200 hover:border-brand-600' }}"
                    >
                        <h3 class="font-semibold text-slate-900">{{ $subject->name }}</h3>
                        <p class="mt-3 text-xs font-semibold text-brand-700">
                            {{ $isCurrentSubject ? 'Browse all years' : 'View Past Papers' }} &rarr;
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.faq-section')
</div>
@endsection
