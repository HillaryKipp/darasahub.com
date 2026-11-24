@extends('layouts.app')

@php
    $popularReady = \App\Models\Paper::query()
        ->verifiedReady()
        ->with(['grade', 'learningArea', 'term', 'examType', 'nationalExam'])
        ->orderByDesc('download_count')
        ->limit(6)
        ->get();

    $seo = [
        'title' => 'Page Not Found (404) | '.\App\Models\SiteSetting::getValue('site_name', 'DarasaHub'),
        'description' => 'The requested exam paper or directory page could not be found.',
        'robots' => 'noindex, follow',
    ];
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
    <p class="text-sm font-bold uppercase tracking-widest text-brand-700">404 &middot; Page Not Found</p>
    <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900">We couldn't find that exam page.</h1>
    <p class="mt-3 text-slate-600 max-w-xl mx-auto text-sm sm:text-base">
        The paper or section may have moved, been archived, or is still undergoing editorial verification.
    </p>

    <div class="mt-6 flex flex-wrap justify-center gap-3">
        <a href="/" class="px-5 py-2.5 rounded-lg bg-brand-700 text-white font-semibold text-sm hover:bg-brand-800">Back to Home</a>
        <a href="/search" class="px-5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 font-semibold text-sm hover:border-brand-600">Search Papers</a>
    </div>

    @if($popularReady->isNotEmpty())
        <div class="mt-12 text-left">
            <h2 class="text-lg font-bold text-slate-900 mb-4 text-center">Popular Verified Papers</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($popularReady as $paper)
                    @include('partials.paper-card', ['paper' => $paper])
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
