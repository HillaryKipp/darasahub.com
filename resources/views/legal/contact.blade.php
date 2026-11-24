@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
    @include('partials.breadcrumbs')

    {{-- Hero Banner --}}
    <section class="rounded-2xl bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white px-6 py-12 sm:px-12 sm:py-14 shadow-sm text-center">
        <div class="max-w-3xl mx-auto space-y-4">
            <span class="inline-flex items-center px-3.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/10 text-brand-100 border border-white/15">
                Darasahub Holdings Limited
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">Contact Us</h1>
            <p class="text-brand-100 text-base sm:text-lg leading-relaxed max-w-2xl mx-auto">
                Partner with Darasahub Holdings Limited to put education technology to work at scale, or reach our editorial team for curriculum corrections, paper submissions, and platform support.
            </p>
        </div>
    </section>

    {{-- Direct Contact Cards --}}
    <section aria-labelledby="contact-details-heading" class="grid gap-5 md:grid-cols-3">
        <h2 id="contact-details-heading" class="sr-only">Direct Contact Details</h2>

        {{-- Phone --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between items-center text-center space-y-4">
            <div class="flex flex-col items-center space-y-3">
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</p>
                    <a href="{{ $contactPhoneHref }}" class="mt-1 inline-block text-lg font-bold text-slate-900 hover:text-brand-700 transition">
                        {{ $contactPhone }}
                    </a>
                </div>
                <p class="text-xs sm:text-sm text-slate-600">
                    Speak directly with our team for institutional, government, or NGO EdTech inquiries.
                </p>
            </div>
            <div class="pt-3 border-t border-slate-100 w-full flex justify-center">
                <a href="{{ $contactPhoneHref }}" class="text-sm font-semibold text-brand-700 hover:underline">
                    Call now &rarr;
                </a>
            </div>
        </div>

        {{-- Email --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between items-center text-center space-y-4">
            <div class="flex flex-col items-center space-y-3">
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</p>
                    <a href="mailto:{{ $contactEmail }}" class="mt-1 inline-block text-lg font-bold text-slate-900 hover:text-brand-700 transition break-all">
                        {{ $contactEmail }}
                    </a>
                </div>
                <p class="text-xs sm:text-sm text-slate-600">
                    Send partnership proposals, curriculum feedback, or general questions anytime.
                </p>
            </div>
            <div class="pt-3 border-t border-slate-100 w-full flex justify-center">
                <a href="mailto:{{ $contactEmail }}" class="text-sm font-semibold text-brand-700 hover:underline">
                    Send an email &rarr;
                </a>
            </div>
        </div>

        {{-- Office & Postal Address --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between items-center text-center space-y-4">
            <div class="flex flex-col items-center space-y-3">
                <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Office &amp; Postal Address</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">1st Floor, Karen Professionals</p>
                    <p class="text-sm font-medium text-slate-700">P.O. Box 20904</p>
                </div>
                <p class="text-xs sm:text-sm text-slate-600">
                    Darasahub Holdings Limited headquarters — Karen, Nairobi, Kenya.
                </p>
            </div>
            <div class="pt-3 border-t border-slate-100 w-full flex justify-center">
                <a href="/about" class="text-sm font-semibold text-brand-700 hover:underline">
                    About our company &rarr;
                </a>
            </div>
        </div>
    </section>

    {{-- Dedicated Inquiry Channels --}}
    <section class="grid gap-6 lg:grid-cols-2">
        {{-- Partnerships & Enterprise EdTech --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 flex flex-col items-center text-center space-y-4">
            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-800">
                Institutions, Governments &amp; NGOs
            </span>
            <h2 class="text-xl font-extrabold text-slate-900">EdTech Partnerships &amp; Platform Delivery</h2>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-lg">
                Looking to launch or scale a digital learning platform? We bring the product design, engineering, and delivery. You bring the learners, content, and reach.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a
                    href="mailto:{{ $contactEmail }}?subject=EdTech%20Partnership%20Inquiry%20-%20Darasahub%20Holdings"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-brand-700 hover:bg-brand-800 text-white font-semibold text-sm transition"
                >
                    Email Partnership Team
                </a>
                <a
                    href="{{ $contactPhoneHref }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-slate-300 bg-white hover:border-brand-600 hover:text-brand-700 text-slate-700 font-semibold text-sm transition"
                >
                    {{ $contactPhone }}
                </a>
            </div>
        </div>

        {{-- Editorial, Corrections & Takedowns --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 flex flex-col items-center text-center space-y-4">
            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                Editorial &amp; Compliance
            </span>
            <h2 class="text-xl font-extrabold text-slate-900">Curriculum Corrections &amp; Takedown Requests</h2>
            <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-lg">
                For curriculum corrections, verified school paper contributions, or general {{ $siteName }} inquiries, email <a href="mailto:{{ $contactEmail }}" class="text-brand-700 font-semibold hover:underline">{{ $contactEmail }}</a>. To report a broken link, inaccurate paper metadata, or submit a copyright takedown request, use our tracked moderation form.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a
                    href="/copyright-takedown"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm transition"
                >
                    Copyright &amp; Takedown Form
                </a>
                <a
                    href="/editorial-policy"
                    class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg border border-slate-300 bg-white hover:border-brand-600 hover:text-brand-700 text-slate-700 font-semibold text-sm transition"
                >
                    Editorial Policy
                </a>
            </div>
        </div>
    </section>

    {{-- Ecosystem Quick Reference --}}
    <section class="bg-slate-100 border border-slate-200 rounded-2xl p-6 sm:p-8 text-center space-y-4">
        <div class="max-w-xl mx-auto">
            <h2 class="text-base sm:text-lg font-bold text-slate-900">Platforms by Darasahub Holdings Limited</h2>
            <p class="mt-1 text-xs sm:text-sm text-slate-600">
                Reach out to us regarding any of our digital education products.
            </p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-3 text-xs sm:text-sm font-semibold">
            <a href="https://play.google.com/store/apps/details?id=com.darasadrive.darasadrive_mobile" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-brand-600 hover:text-brand-700 transition">
                DarasaDrive Mobile &nearr;
            </a>
            <a href="https://cbcschemes.co.ke/" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-brand-600 hover:text-brand-700 transition">
                CBC Schemes &nearr;
            </a>
            <a href="https://easytvethub.com" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-800 hover:border-brand-600 hover:text-brand-700 transition">
                EasyTVET Hub &nearr;
            </a>
        </div>
    </section>
</div>
@endsection
