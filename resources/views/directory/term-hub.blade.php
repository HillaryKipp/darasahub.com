@extends('layouts.app')

@section('content')
<div
    class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
    x-data="{ activeExam: 'all' }"
>
    @include('partials.breadcrumbs')

    <header class="mb-6">
        <div class="flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-wide text-brand-700 mb-2">
            <span>{{ $grade->name }}</span>
            <span class="text-slate-300">/</span>
            <span>{{ $area->name }}</span>
            <span class="text-slate-300">/</span>
            <span>{{ $term->name }}</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $h1 }}</h1>
    </header>

    {{-- Editorial / Contextual Paragraph --}}
    @if(!empty($content) && $content->isApprovedForPublic() && !empty(trim((string) $content->intro)))
        <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 mb-6 text-sm sm:text-base text-slate-700 leading-relaxed space-y-3">
            {!! nl2br(e($content->intro)) !!}
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 mb-6 text-sm sm:text-base text-slate-700 leading-relaxed">
            <p>
                Explore all verified <strong>{{ $grade->name }} {{ $area->name }} {{ $term->name }}</strong> assessments in one place.
                @if($totalPapers > 0)
                    There {{ $totalPapers === 1 ? 'is' : 'are' }} currently <strong>{{ $totalPapers }}</strong> verified {{ \Illuminate\Support\Str::plural('paper', $totalPapers) }} available below.
                    Click any paper title to open and read it directly in your browser without downloading, or use the <em>Download</em> button whenever you want to save a copy for offline revision.
                @else
                    Verified assessments for this term are currently being reviewed and will appear here as soon as they are published.
                @endif
            </p>
        </div>
    @endif

    {{-- Exam Type Status Buttons (Opener, Midterm, End Term) --}}
    <section aria-labelledby="exam-types-heading" class="bg-white border border-slate-200 rounded-xl p-5 mb-8">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h2 id="exam-types-heading" class="text-sm font-bold text-slate-900">
                {{ $term->name }} Assessment Types
            </h2>
            <span class="inline-flex items-center gap-3 text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1.5 text-brand-800 font-medium">
                    <span class="inline-block w-2 h-2 rounded-full bg-brand-700"></span>
                    Available
                </span>
                <span class="inline-flex items-center gap-1.5 text-slate-400">
                    <span class="inline-block w-2 h-2 rounded-full bg-slate-300"></span>
                    Not available yet
                </span>
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @foreach($examTypeSections as $section)
                @php($examType = $section['exam_type'])
                @include('partials.status-check-button', [
                    'label' => $examType->name,
                    'checked' => $section['available'],
                    'count' => $section['available'] ? $section['count'] : null,
                    'asButton' => true,
                    'alpineClick' => "activeExam = (activeExam === '{$examType->slug}' ? 'all' : '{$examType->slug}')",
                    'alpineActive' => "activeExam === '{$examType->slug}'",
                    'unavailableText' => "No verified {$grade->name} {$area->name} {$term->name} {$examType->name} available yet",
                ])
            @endforeach

            @if($examTypeSections->where('available', true)->count() > 1)
                <button
                    type="button"
                    x-show="activeExam !== 'all'"
                    x-cloak
                    @click="activeExam = 'all'"
                    class="ml-auto text-xs font-semibold text-brand-700 hover:underline"
                >
                    Show all available &rarr;
                </button>
            @endif
        </div>
    </section>

    {{-- Bundled Papers Grouped by Available Exam Type --}}
    @if($totalPapers > 0)
        <div class="space-y-8">
            @foreach($examTypeSections as $section)
                @if($section['available'])
                    @php($examType = $section['exam_type'])
                    <section
                        id="{{ $examType->slug }}"
                        aria-labelledby="heading-{{ $examType->slug }}"
                        x-show="activeExam === 'all' || activeExam === '{{ $examType->slug }}'"
                    >
                        <div class="flex flex-wrap items-baseline justify-between gap-2 mb-3">
                            <h2 id="heading-{{ $examType->slug }}" class="text-lg sm:text-xl font-bold text-slate-900">
                                {{ $examType->name }}
                                <span class="text-sm font-medium text-slate-500">({{ $section['count'] }})</span>
                            </h2>
                        </div>

                        @include('partials.paper-paragraph-list', ['papers' => $section['papers']])
                    </section>
                @endif
            @endforeach
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-xl p-6 text-sm text-slate-600">
            No verified papers are published for {{ $grade->name }} {{ $area->name }} {{ $term->name }} yet.
        </div>
    @endif

    {{-- Grade Learning Areas Grid (matching /{grade} hub) --}}
    @if(!empty($gradeLearningAreas) && $gradeLearningAreas->isNotEmpty())
        <section aria-labelledby="grade-subjects-heading" class="mt-10">
            <h2 id="grade-subjects-heading" class="text-lg font-bold text-slate-900 mb-4">
                {{ $grade->name }} Learning Areas
            </h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($gradeLearningAreas as $gradeArea)
                    @php($isCurrentArea = $gradeArea->id === $area->id)
                    <a
                        href="/{{ $grade->slug }}/{{ $gradeArea->slug }}"
                        class="rounded-xl p-5 border transition block {{ $isCurrentArea ? 'bg-brand-50/40 border-brand-600/60' : 'bg-white border-slate-200 hover:border-brand-600' }}"
                    >
                        <h3 class="font-semibold text-slate-900">{{ $gradeArea->name }}</h3>
                        @if($gradeArea->pivot?->pathway)
                            <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                {{ \App\Models\GradeLearningArea::PATHWAYS[$gradeArea->pivot->pathway] ?? $gradeArea->pivot->pathway }}
                            </span>
                        @endif
                        <p class="mt-3 text-xs font-semibold text-brand-700">
                            {{ $isCurrentArea ? 'Browse all terms' : 'Browse Terms' }} &rarr;
                        </p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.faq-section')
</div>
@endsection
