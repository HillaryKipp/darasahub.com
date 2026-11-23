@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">{{ $h1 }}</h1>

    @include('partials.editorial-intro')

    @if($papers->isNotEmpty())
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($papers as $paper)
                @include('partials.paper-card', ['paper' => $paper])
            @endforeach
        </div>
        <div class="mt-8">
            {{ $papers->links() }}
        </div>
    @endif

    @if($adjacentYears->isNotEmpty())
        <section class="mt-10 bg-white border border-slate-200 rounded-xl p-5">
            <h2 class="text-sm font-bold text-slate-900 mb-3">Other Available Years for {{ $exam->name }} {{ $area->name }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($adjacentYears as $yr)
                    <a href="/{{ $exam->slug }}/{{ $area->slug }}-past-papers/{{ $yr }}" class="px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-sm font-medium text-brand-700 hover:border-brand-600">
                        {{ $yr }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <x-internal-links :groups="$internalLinks ?? []" />

    @include('partials.faq-section')
</div>
@endsection
