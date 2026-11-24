@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

    {{-- Exam Type Check Buttons (Opener, Midterm, End Term) --}}
    @if(!empty($examTypeButtons))
        <section aria-label="Assessment types" class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <span class="text-xs font-bold uppercase tracking-wide text-slate-600">{{ $term->name }} Assessments</span>
                <a href="/{{ $grade->slug }}/{{ $area->slug }}/{{ $term->slug }}" class="text-xs font-semibold text-brand-700 hover:underline">
                    View all {{ $term->name }} exams together &rarr;
                </a>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                @foreach($examTypeButtons as $btn)
                    @php($et = $btn['exam_type'])
                    @include('partials.status-check-button', [
                        'label' => $et->name,
                        'checked' => $btn['available'],
                        'active' => $btn['active'],
                        'count' => $btn['available'] ? $btn['count'] : null,
                        'href' => $btn['available'] ? "/{$grade->slug}/{$area->slug}/{$term->slug}/{$et->slug}" : null,
                        'unavailableText' => "No verified {$grade->name} {$area->name} {$term->name} {$et->name} available yet",
                    ])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Readable Intro Paragraph --}}
    @if(!empty($content) && $content->isApprovedForPublic() && !empty(trim((string) $content->intro)))
        <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 mb-8 text-sm sm:text-base text-slate-700 leading-relaxed space-y-3">
            {!! nl2br(e($content->intro)) !!}
        </div>
    @else
        <div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 mb-8 text-sm sm:text-base text-slate-700 leading-relaxed">
            <p>
                Access verified <strong>{{ $grade->name }} {{ $area->name }} {{ $term->name }} {{ $examType->name }}</strong> aligned to the Kenyan Competency-Based Curriculum (CBC).
                @if($papers->total() > 0)
                    Below {{ $papers->total() === 1 ? 'is' : 'are' }} <strong>{{ $papers->total() }}</strong> verified {{ \Illuminate\Support\Str::plural('paper', $papers->total()) }} ready for revision and classroom assessment.
                    You can click any paper link to open and read it directly in your browser without downloading, or choose the <em>Download</em> button to save a copy to your device.
                @else
                    Verified papers for this assessment are currently being reviewed and will be listed here once approved.
                @endif
            </p>
        </div>
    @endif

    {{-- Available Papers in Paragraph Layout --}}
    @if($papers->isNotEmpty())
        <section aria-labelledby="papers-list-heading">
            <h2 id="papers-list-heading" class="text-lg font-bold text-slate-900 mb-4">
                Available Papers ({{ $papers->total() }})
            </h2>

            @include('partials.paper-paragraph-list', ['papers' => $papers->getCollection()])

            @if($papers->hasPages())
                <div class="mt-8">
                    {{ $papers->links() }}
                </div>
            @endif
        </section>
    @endif

    <div class="mt-8 pt-6 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-sm">
        <a href="/{{ $grade->slug }}/{{ $area->slug }}/{{ $term->slug }}" class="font-semibold text-brand-700 hover:underline">
            &larr; Back to all {{ $grade->name }} {{ $area->name }} {{ $term->name }} Exams
        </a>
        <a href="/" class="text-slate-600 hover:text-brand-700 hover:underline">
            Browse all CBC Grades &rarr;
        </a>
    </div>

    @include('partials.faq-section')
</div>
@endsection
