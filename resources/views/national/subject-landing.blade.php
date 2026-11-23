@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">{{ $h1 }}</h1>

    @include('partials.editorial-intro')

    @if($readyYears->isNotEmpty())
        <section class="mb-8" aria-labelledby="years-heading">
            <h2 id="years-heading" class="text-sm font-bold uppercase tracking-wide text-slate-500 mb-3">Browse by Year</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($readyYears as $yr)
                    <a href="/{{ $exam->slug }}/{{ $area->slug }}-past-papers/{{ $yr }}" class="px-3.5 py-1.5 rounded-lg bg-white border border-slate-200 text-sm font-semibold text-brand-800 hover:border-brand-600">
                        {{ $yr }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($papers->isNotEmpty())
        <section aria-labelledby="national-papers-heading">
            <h2 id="national-papers-heading" class="text-lg font-bold text-slate-900 mb-4">Available Papers ({{ $papers->total() }})</h2>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($papers as $paper)
                    @include('partials.paper-card', ['paper' => $paper])
                @endforeach
            </div>
            <div class="mt-8">
                {{ $papers->links() }}
            </div>
        </section>
    @endif

    <x-internal-links :groups="$internalLinks ?? []" />

    @include('partials.faq-section')
</div>
@endsection
