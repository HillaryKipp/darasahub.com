@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 text-center">Privacy Policy</h1>
        <p>{{ $siteName }} is used by students, teachers, and parents across Kenya. We intentionally design this platform to work without user accounts and to collect the minimum data necessary to operate safely.</p>
        <h2 class="text-lg font-bold text-slate-900 pt-2">Information We Collect</h2>
        <ul class="list-disc pl-5 space-y-1">
            <li><strong>Browsing &amp; Downloads:</strong> Basic server logs and aggregated download counters used for security, abuse prevention, and ranking popular resources.</li>
            <li><strong>Takedown &amp; Problem Reports:</strong> Name, email, and message details you voluntarily submit via the Copyright &amp; Takedown form, used solely to review and resolve your request.</li>
        </ul>
        <h2 class="text-lg font-bold text-slate-900 pt-2">Children &amp; Learners</h2>
        <p>No registration, personal profile, or behavioral tracking is required to browse or download revision papers. For privacy inquiries, email <a href="mailto:{{ $contactEmail }}" class="text-brand-700 hover:underline">{{ $contactEmail }}</a>.</p>
    </div>
</div>
@endsection
