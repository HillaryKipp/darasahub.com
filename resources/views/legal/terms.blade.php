@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 text-center">Terms of Use</h1>
        <p>All past papers, marking schemes, and study guides indexed on {{ $siteName }} are provided strictly for personal revision, classroom instruction, and educational reference.</p>
        <p>You may not scrape, mirror, or redistribute content in violation of the original rights holder's terms. Automated bulk downloading is rate-limited to preserve fair access for learners.</p>
        <p>While we verify links and curriculum classifications, {{ $siteName }} provides materials on an "as-is" basis without warranty. Report any errors through our <a href="/copyright-takedown" class="text-brand-700 hover:underline">report form</a>.</p>
    </div>
</div>
@endsection
