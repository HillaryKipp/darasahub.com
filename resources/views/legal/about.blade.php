@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
    @include('partials.breadcrumbs')

    {{-- Hero Section --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-8 sm:p-12 shadow-sm">
        <div class="max-w-3xl space-y-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-white/10 text-brand-100 border border-white/15">
                Darasahub Holdings Limited
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                Transforming the Learning Experience Across Africa
            </h1>
            <p class="text-brand-100 text-base sm:text-lg leading-relaxed">
                Darasahub Holdings Limited is a leading Education Technology (EdTech) company transforming the learning experience across Africa. We design, build, and manage digital platforms that make education more accessible, engaging, and efficient for learners, educators, and institutions.
            </p>
            <div class="pt-2 flex flex-wrap items-center gap-3">
                <a href="/contact" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-white text-brand-900 font-semibold text-sm hover:bg-brand-50 transition">
                    Partner With Us
                </a>
                <a href="#our-projects" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-brand-900/50 text-white border border-white/20 font-semibold text-sm hover:bg-brand-900/70 transition">
                    Explore Our Projects
                </a>
            </div>
        </div>
    </section>

    {{-- Who We Work With & Delivery Model --}}
    <section class="grid gap-6 lg:grid-cols-12 items-stretch">
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 flex flex-col justify-between space-y-5">
            <div class="space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-700">Our Partnership Model</p>
                <h2 class="text-2xl font-extrabold text-slate-900">
                    Putting Education Technology to Work at Scale
                </h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                    We work with <strong class="text-slate-900">institutions, governments, and NGOs</strong> that want to put education technology to work at scale. From national exam repositories and teacher SaaS tools to mobile learning ecosystems, we build resilient platforms tailored for African connectivity, curricula, and classrooms.
                </p>
            </div>

            <div class="rounded-xl bg-brand-50 border border-brand-100 p-5">
                <p class="text-base sm:text-lg font-bold text-brand-900 leading-snug">
                    &ldquo;We bring the product design, engineering, and delivery. You bring the learners, content, and reach.&rdquo;
                </p>
            </div>
        </div>

        <div class="lg:col-span-5 grid gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="shrink-0 w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Product Design</h3>
                    <p class="mt-1 text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Intuitive, mobile-first learning interfaces crafted around real workflows of teachers, learners, and school administrators.
                    </p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="shrink-0 w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Engineering</h3>
                    <p class="mt-1 text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Fast, reliable web, SaaS, and mobile engineering built to serve high-volume national and institutional traffic.
                    </p>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 flex items-start gap-4">
                <div class="shrink-0 w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">End-to-End Delivery</h3>
                    <p class="mt-1 text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Ongoing platform operations, content verification systems, and continuous improvement after launch.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Top Projects --}}
    <section id="our-projects" aria-labelledby="projects-heading" class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-brand-700">Portfolio</p>
                <h2 id="projects-heading" class="text-2xl font-extrabold text-slate-900">Our Top Projects</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-500">
                Digital education platforms built and managed by Darasahub Holdings Limited.
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            {{-- Project 1: DarasaDrive Mobile --}}
            <article class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between hover:border-brand-600 transition">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide rounded-full bg-brand-50 text-brand-800">
                            Mobile App
                        </span>
                        <span class="text-xs text-slate-400">Google Play</span>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">DarasaDrive Mobile</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        Our flagship Android learning companion on Google Play, giving learners and educators convenient mobile access to organized study materials and revision resources anytime, anywhere.
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a
                        href="https://play.google.com/store/apps/details?id=com.darasadrive.darasadrive_mobile"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900 hover:underline"
                    >
                        View on Google Play
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>

            {{-- Project 2: CBC Schemes --}}
            <article class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between hover:border-brand-600 transition">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide rounded-full bg-brand-50 text-brand-800">
                            Teacher SaaS
                        </span>
                        <span class="text-xs text-slate-400">cbcschemes.co.ke</span>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">CBC Schemes</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        A dedicated SaaS platform built for Kenyan Competency-Based Curriculum (CBC) teachers to quickly generate structured, curriculum-aligned schemes of work and save hours of preparation time.
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a
                        href="https://cbcschemes.co.ke/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900 hover:underline"
                    >
                        Visit cbcschemes.co.ke
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>

            {{-- Project 3: EasyTVET Hub --}}
            <article class="bg-white border border-slate-200 rounded-2xl p-6 flex flex-col justify-between hover:border-brand-600 transition">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide rounded-full bg-brand-50 text-brand-800">
                            TVET Platform
                        </span>
                        <span class="text-xs text-slate-400">easytvethub.com</span>
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900">EasyTVET Hub</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                        A comprehensive past papers directory and course guideline platform tailored for Kenyan TVET institutions, trainers, and trainees seeking structured technical and vocational exam resources.
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-100">
                    <a
                        href="https://easytvethub.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900 hover:underline"
                    >
                        Visit easytvethub.com
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </article>
        </div>
    </section>

    {{-- About DarasaHub.com & Editorial Standards --}}
    <section class="bg-white border border-slate-200 rounded-2xl p-6 sm:p-8 space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed">
        <h2 class="text-xl font-extrabold text-slate-900">About {{ $siteName }} &amp; Our Verification Standards</h2>
        <p>
            {{ $siteName }} is operated by <strong>Darasahub Holdings Limited</strong> as a quality-verified educational directory helping Kenyan learners, parents, and teachers locate Competency-Based Curriculum (CBC) term assessments (PP1 to Grade 12) and national examination past papers (KPSEA, KILEA, KJSEA, and KCSE).
        </p>
        <p>
            Every landing page and assessment listed on {{ $siteName }} goes through a content-readiness and verification review: we confirm curriculum alignment, working file access, source attribution, and permission status before publishing. Read more in our <a href="/editorial-policy" class="text-brand-700 font-semibold hover:underline">Editorial &amp; Verification Policy</a>.
        </p>
        <p class="text-xs sm:text-sm text-slate-500 pt-2 border-t border-slate-100">
            {{ $siteName }} and Darasahub Holdings Limited are independent educational technology providers and are not affiliated with the Kenya National Examinations Council (KNEC) or the Kenya Institute of Curriculum Development (KICD).
        </p>
    </section>

    {{-- Bottom Partnership CTA --}}
    <section class="rounded-2xl bg-slate-900 text-white p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-1.5 max-w-2xl">
            <h2 class="text-xl sm:text-2xl font-extrabold">Ready to build or scale an EdTech platform?</h2>
            <p class="text-sm text-slate-300">
                Talk to Darasahub Holdings Limited about institutional, government, or NGO digital learning initiatives.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3 shrink-0">
            <a href="/contact" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm transition">
                Contact Us
            </a>
            <a href="mailto:{{ $contactEmail }}" class="inline-flex items-center px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-100 font-medium text-sm transition">
                {{ $contactEmail }}
            </a>
        </div>
    </section>
</div>
@endsection
