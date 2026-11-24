@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 text-center">Editorial &amp; Verification Policy</h1>
        <p>{{ $siteName }} applies a strict quality gate to every exam paper and curriculum page:</p>
        <ul class="list-disc pl-5 space-y-1">
            <li><strong>Verified Curriculum Mapping:</strong> Learning areas per grade follow official KICD CBC structures, and legacy 8-4-4 KCSE papers are explicitly labelled separately from CBC assessments.</li>
            <li><strong>Source Attribution &amp; Permission:</strong> Every published paper records its source and permission status. Papers with unclear rights stay unpublished.</li>
            <li><strong>No Thin or Placeholder Pages:</strong> Pages without verified resources and human-reviewed editorial context remain unpublished or non-indexed.</li>
            <li><strong>Ongoing Link Monitoring:</strong> Automated link checks, manual reviews, and user reports keep broken or gated files out of the directory.</li>
        </ul>
    </div>
</div>
@endsection
