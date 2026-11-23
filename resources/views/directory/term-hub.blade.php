@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">{{ $h1 }}</h1>

    @include('partials.editorial-intro')

    @if($readyExamTypes->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach($readyExamTypes as $examType)
                <a href="/{{ $grade->slug }}/{{ $area->slug }}/{{ $term->slug }}/{{ $examType->slug }}" class="bg-white border border-slate-200 rounded-xl p-5 hover:border-brand-600 transition block">
                    <h2 class="font-bold text-slate-900">{{ $examType->name }}</h2>
                    <p class="mt-2 text-xs font-semibold text-brand-700">View Verified Papers &rarr;</p>
                </a>
            @endforeach
        </div>
    @endif

    @include('partials.faq-section')
</div>
@endsection
