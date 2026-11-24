@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4 text-center">{{ $h1 }}</h1>

    @include('partials.editorial-intro')

    @if($readyAreas->isNotEmpty())
        <section aria-labelledby="grade-subjects-heading">
            <h2 id="grade-subjects-heading" class="text-lg font-bold text-slate-900 mb-4 text-center">{{ $grade->name }} Learning Areas</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($readyAreas as $area)
                    <a href="/{{ $grade->slug }}/{{ $area->slug }}" class="bg-white border border-slate-200 rounded-xl p-5 hover:border-brand-600 transition flex flex-col items-center text-center">
                        <h3 class="font-semibold text-slate-900">{{ $area->name }}</h3>
                        @if($area->pivot?->pathway)
                            <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                {{ \App\Models\GradeLearningArea::PATHWAYS[$area->pivot->pathway] ?? $area->pivot->pathway }}
                            </span>
                        @endif
                        <p class="mt-3 text-xs font-semibold text-brand-700">Browse Terms &rarr;</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.faq-section')
</div>
@endsection
