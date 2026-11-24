@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <article class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex flex-wrap items-center justify-center gap-2 text-xs mb-3">
            <span class="px-2.5 py-1 rounded-full bg-brand-50 text-brand-800 font-semibold">{{ $paper->year }}</span>
            <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">
                {{ $paper->file_type === \App\Models\Paper::FILE_TYPE_MARKING_SCHEME ? 'Marking Scheme' : 'Question Paper' }}
            </span>
            <span class="px-2.5 py-1 rounded-full {{ $paper->curriculum === \App\Models\Paper::CURRICULUM_LEGACY_844 ? 'bg-amber-50 text-amber-800' : 'bg-emerald-50 text-emerald-800' }} font-medium">
                {{ $paper->curriculum === \App\Models\Paper::CURRICULUM_LEGACY_844 ? '8-4-4 Legacy Curriculum' : 'CBC Curriculum' }}
            </span>
            @if($paper->paper_number)
                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">{{ $paper->paper_number }}</span>
            @endif
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight text-center">{{ $paper->title }}</h1>

        <dl class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 text-sm text-center bg-slate-50 rounded-xl p-4 border border-slate-100">
            @if($paper->grade)
                <div>
                    <dt class="text-xs text-slate-500">Grade / Level</dt>
                    <dd class="font-semibold text-slate-900">{{ $paper->grade->name }}</dd>
                </div>
            @endif
            @if($paper->nationalExam)
                <div>
                    <dt class="text-xs text-slate-500">National Exam</dt>
                    <dd class="font-semibold text-slate-900">{{ $paper->nationalExam->name }} ({{ $paper->nationalExam->full_name }})</dd>
                </div>
            @endif
            @if($paper->learningArea)
                <div>
                    <dt class="text-xs text-slate-500">Learning Area / Subject</dt>
                    <dd class="font-semibold text-slate-900">{{ $paper->learningArea->name }}</dd>
                </div>
            @endif
            @if($paper->term && $paper->examType)
                <div>
                    <dt class="text-xs text-slate-500">Assessment</dt>
                    <dd class="font-semibold text-slate-900">{{ $paper->term->name }} &middot; {{ $paper->examType->name }}</dd>
                </div>
            @endif
            <div>
                <dt class="text-xs text-slate-500">Source Attribution</dt>
                <dd class="font-semibold text-slate-900">
                    @if($paper->source_url)
                        <a href="{{ $paper->source_url }}" rel="nofollow noopener noreferrer" target="_blank" class="text-brand-700 hover:underline">{{ $paper->source_name ?: 'Verified Source' }}</a>
                    @else
                        {{ $paper->source_name ?: 'Verified Source' }}
                    @endif
                </dd>
            </div>
            @if($paper->formatted_file_size)
                <div>
                    <dt class="text-xs text-slate-500">File Size</dt>
                    <dd class="font-semibold text-slate-900">{{ $paper->formatted_file_size }}</dd>
                </div>
            @endif
        </dl>

        @if($paper->description)
            <div class="mt-6 text-slate-700 leading-relaxed text-sm sm:text-base space-y-3 text-center max-w-3xl mx-auto">
                {!! nl2br(e($paper->description)) !!}
            </div>
        @endif

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            @if($paper->view_url && $paper->hasWorkingResource())
                <a
                    href="{{ $paper->view_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg border border-brand-700 text-brand-800 bg-white hover:bg-brand-50 font-semibold text-sm transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span>Open {{ $paper->file_type === \App\Models\Paper::FILE_TYPE_MARKING_SCHEME ? 'Marking Scheme' : 'Question Paper' }}</span>
                </a>
            @endif

            @if($paper->hasWorkingResource())
                <a
                    href="/download/{{ $paper->id }}"
                    rel="nofollow"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm shadow-sm transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download {{ $paper->file_type === \App\Models\Paper::FILE_TYPE_MARKING_SCHEME ? 'Marking Scheme' : 'Question Paper' }}</span>
                </a>
            @endif

            @if($paper->relatedPaper && $paper->relatedPaper->isVerifiedReady() && $paper->relatedPaper->view_url)
                <a
                    href="{{ $paper->relatedPaper->view_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center px-5 py-3 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-sm transition"
                >
                    Open {{ $paper->relatedPaper->file_type === \App\Models\Paper::FILE_TYPE_MARKING_SCHEME ? 'Marking Scheme' : 'Question Paper' }}
                </a>
            @endif

            <a
                href="/copyright-takedown?paper={{ $paper->id }}"
                class="w-full text-center text-xs text-slate-500 hover:text-red-700 hover:underline mt-2"
            >
                Report a problem / copyright issue
            </a>
        </div>

        @if($paper->hasWorkingResource() && $paper->preview_url)
            <div x-data="{ showPreview: true }" class="mt-8 border-t border-slate-200 pt-6 text-center">
                <button
                    type="button"
                    @click="showPreview = !showPreview"
                    class="text-sm font-semibold text-brand-700 hover:underline"
                >
                    <span x-show="!showPreview" x-cloak>Show PDF Preview</span>
                    <span x-show="showPreview">Hide PDF Preview</span>
                </button>
                <template x-if="showPreview">
                    <div class="mt-4 rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                        <iframe
                            src="{{ $paper->preview_url }}"
                            title="PDF preview for {{ $paper->title }}"
                            loading="lazy"
                            class="w-full h-[640px]"
                        ></iframe>
                    </div>
                </template>
            </div>
        @endif
    </article>

    <div class="mt-8 flex items-center justify-center text-sm">
        <a href="{{ $parentLandingUrl }}" class="font-semibold text-brand-700 hover:underline">&larr; Back to {{ $parentLandingLabel }}</a>
    </div>

    @if($relatedPapers->isNotEmpty())
        <section class="mt-10" aria-labelledby="related-papers-heading">
            <h2 id="related-papers-heading" class="text-lg font-bold text-slate-900 mb-4 text-center">Related Verified Papers</h2>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($relatedPapers as $related)
                    @include('partials.paper-card', ['paper' => $related])
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
