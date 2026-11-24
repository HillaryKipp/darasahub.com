@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4 text-center">{{ $h1 }}</h1>

    @include('partials.editorial-intro')

    @if($readySubjects->isNotEmpty())
        <section aria-labelledby="national-subjects-heading">
            <h2 id="national-subjects-heading" class="text-lg font-bold text-slate-900 mb-4 text-center">{{ $exam->name }} Subjects</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($readySubjects as $area)
                    <a href="/{{ $exam->slug }}/{{ $area->slug }}-past-papers" class="bg-white border border-slate-200 rounded-xl p-5 hover:border-brand-600 transition flex flex-col items-center text-center">
                        <h3 class="font-semibold text-slate-900">{{ $exam->name }} {{ $area->name }} Past Papers</h3>
                        <p class="mt-2 text-xs font-semibold text-brand-700">View Available Years &rarr;</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.faq-section')
</div>
@endsection
