@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">About {{ $siteName }}</h1>
        <p>{{ $siteName }} is an independent educational directory built to help Kenyan learners, parents, and teachers locate verified Competency-Based Curriculum (CBC) term assessments (PP1 to Grade 12) and national examination past papers (KPSEA, KILEA, KJSEA, and KCSE).</p>
        <p>Unlike directories that auto-publish unverified stubs, every landing page and paper listed on {{ $siteName }} passes a content-readiness and verification review: we confirm curriculum alignment, working file access, source attribution, and permission status before publishing.</p>
        <p>{{ $siteName }} is not affiliated with the Kenya National Examinations Council (KNEC) or the Kenya Institute of Curriculum Development (KICD).</p>
    </div>
</div>
@endsection
