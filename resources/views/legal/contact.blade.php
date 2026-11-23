@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')
    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Contact {{ $siteName }}</h1>
        <p>For curriculum corrections, verified school paper contributions, or general inquiries, contact our editorial team at <a href="mailto:{{ $contactEmail }}" class="text-brand-700 font-semibold hover:underline">{{ $contactEmail }}</a>.</p>
        <p>To report a broken link, inaccurate paper metadata, or submit a copyright takedown request, please use our dedicated <a href="/copyright-takedown" class="text-brand-700 font-semibold hover:underline">Copyright &amp; Takedown form</a> so your request is tracked in our moderation queue.</p>
    </div>
</div>
@endsection
