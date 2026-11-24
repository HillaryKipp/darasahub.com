@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Copyright, Takedown &amp; Problem Report</h1>
            <p class="mt-2 text-sm text-slate-600 leading-relaxed max-w-2xl mx-auto">
                {{ $siteName }} respects intellectual property rights and curriculum accuracy. If you are a rights holder requesting removal of a document, or a learner/teacher reporting a broken link or mislabelled paper, submit the form below. Verified copyright takedown requests are prioritized promptly.
            </p>
        </div>

        @if(session('status'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-900 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/copyright-takedown" class="space-y-4">
            @csrf
            @if($paper)
                <input type="hidden" name="paper_id" value="{{ $paper->id }}">
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 text-sm">
                    Reporting paper: <strong>{{ $paper->title }}</strong>
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="requester_name" class="block text-sm font-medium text-slate-700 mb-1">Your Name *</label>
                    <input id="requester_name" name="requester_name" type="text" required value="{{ old('requester_name') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="requester_email" class="block text-sm font-medium text-slate-700 mb-1">Email Address *</label>
                    <input id="requester_email" name="requester_email" type="email" required value="{{ old('requester_email') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="organization" class="block text-sm font-medium text-slate-700 mb-1">School / Organization (optional)</label>
                    <input id="organization" name="organization" type="text" value="{{ old('organization') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="reason_type" class="block text-sm font-medium text-slate-700 mb-1">Reason *</label>
                    <select id="reason_type" name="reason_type" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white">
                        <option value="copyright" @selected(old('reason_type') === 'copyright')>Copyright / Ownership Takedown</option>
                        <option value="broken_link" @selected(old('reason_type') === 'broken_link')>Broken or Login-Gated File Link</option>
                        <option value="inaccurate" @selected(old('reason_type') === 'inaccurate')>Wrong Grade / Subject / Year</option>
                        <option value="other" @selected(old('reason_type') === 'other')>Other Issue</option>
                    </select>
                </div>
            </div>

            <div>
                <label for="paper_url" class="block text-sm font-medium text-slate-700 mb-1">Page URL (optional if linked above)</label>
                <input id="paper_url" name="paper_url" type="url" value="{{ old('paper_url', $paper?->url_path ? url($paper->url_path) : '') }}" placeholder="https://darasahub.com/..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>

            <div>
                <label for="details" class="block text-sm font-medium text-slate-700 mb-1">Details *</label>
                <textarea id="details" name="details" rows="5" required minlength="15" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Describe the copyright claim or exact problem with this resource...">{{ old('details') }}</textarea>
            </div>

            <button type="submit" class="px-5 py-2.5 rounded-lg bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm transition">
                Submit Request
            </button>
        </form>
    </div>
</div>
@endsection
