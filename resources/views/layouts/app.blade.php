<!DOCTYPE html>
<html lang="en-KE">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seo['title'] ?? config('app.name', 'DarasaHub') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? 'Kenya CBC Past Papers, Term Assessments and National Exams Directory.' }}">
    <meta name="robots" content="{{ $seo['robots'] ?? 'noindex, follow' }}">
    @if(!empty($seo['canonical']))
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @endif

    {{-- Open Graph & Twitter Cards --}}
    <meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
    <meta property="og:site_name" content="{{ \App\Models\SiteSetting::getValue('site_name', 'DarasaHub') }}">
    <meta property="og:title" content="{{ $seo['title'] ?? config('app.name', 'DarasaHub') }}">
    <meta property="og:description" content="{{ $seo['description'] ?? '' }}">
    @if(!empty($seo['canonical']))
        <meta property="og:url" content="{{ $seo['canonical'] }}">
    @endif
    @if(!empty($seo['og_image']))
        <meta property="og:image" content="{{ url($seo['og_image']) }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ url($seo['og_image']) }}">
    @else
        <meta name="twitter:card" content="summary">
    @endif
    <meta name="twitter:title" content="{{ $seo['title'] ?? config('app.name', 'DarasaHub') }}">
    <meta name="twitter:description" content="{{ $seo['description'] ?? '' }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(!empty($seo['schemas']))
        @foreach($seo['schemas'] as $schema)
            <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
        @endforeach
    @endif
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased">
    {{-- Sticky Header (Section 9) --}}
    <header x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-2 font-bold text-lg text-brand-800 tracking-tight">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-brand-700 text-white text-sm font-extrabold">DH</span>
                    <span>{{ \App\Models\SiteSetting::getValue('site_name', 'DarasaHub') }}</span>
                </a>

                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-700" aria-label="Primary">
                    <a href="/" class="hover:text-brand-700 transition">Home</a>
                    <a href="/kpsea" class="hover:text-brand-700 transition">KPSEA</a>
                    <a href="/kilea" class="hover:text-brand-700 transition">KILEA</a>
                    <a href="/kjsea" class="hover:text-brand-700 transition">KJSEA</a>
                    <a href="/kcse" class="hover:text-brand-700 transition">KCSE</a>
                    <a href="/latest" class="hover:text-brand-700 transition">Latest</a>
                    <a href="/search" class="inline-flex items-center px-3 py-1.5 rounded-md bg-brand-50 text-brand-800 hover:bg-brand-100 transition">Search</a>
                </nav>

                <button
                    type="button"
                    @click="open = !open"
                    class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-600 hover:text-slate-900 hover:bg-slate-100"
                    :aria-expanded="open.toString()"
                    aria-label="Toggle navigation menu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="open" x-cloak class="md:hidden border-t border-slate-200 bg-white">
            <nav class="px-4 py-3 space-y-2 text-sm font-medium text-slate-700" aria-label="Mobile navigation">
                <a href="/" class="block py-1.5 hover:text-brand-700">Home</a>
                <a href="/kpsea" class="block py-1.5 hover:text-brand-700">KPSEA</a>
                <a href="/kilea" class="block py-1.5 hover:text-brand-700">KILEA</a>
                <a href="/kjsea" class="block py-1.5 hover:text-brand-700">KJSEA</a>
                <a href="/kcse" class="block py-1.5 hover:text-brand-700">KCSE</a>
                <a href="/latest" class="block py-1.5 hover:text-brand-700">Latest Verified Papers</a>
                <a href="/search" class="block py-1.5 text-brand-700 font-semibold">Search Papers</a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 bg-slate-900 text-slate-300 border-t border-slate-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid gap-8 md:grid-cols-4 text-sm">
            <div class="md:col-span-2">
                <p class="font-bold text-white text-base">{{ \App\Models\SiteSetting::getValue('site_name', 'DarasaHub') }}</p>
                <p class="mt-2 text-slate-400 leading-relaxed">
                    An organized, quality-verified directory of Kenyan CBC term assessments (PP1–Grade 12) and national examination past papers. No accounts required; minimal data collected.
                </p>
            </div>
            <div>
                <p class="font-semibold text-white">National Exams</p>
                <ul class="mt-3 space-y-2">
                    <li><a href="/kpsea" class="hover:text-white">KPSEA Past Papers</a></li>
                    <li><a href="/kilea" class="hover:text-white">KILEA Past Papers</a></li>
                    <li><a href="/kjsea" class="hover:text-white">KJSEA Past Papers</a></li>
                    <li><a href="/kcse" class="hover:text-white">KCSE Past Papers</a></li>
                </ul>
            </div>
            <div>
                <p class="font-semibold text-white">Trust &amp; Policies</p>
                <ul class="mt-3 space-y-2">
                    <li><a href="/about" class="hover:text-white">About Us</a></li>
                    <li><a href="/editorial-policy" class="hover:text-white">Editorial Policy</a></li>
                    <li><a href="/copyright-takedown" class="hover:text-white">Copyright &amp; Takedown</a></li>
                    <li><a href="/privacy-policy" class="hover:text-white">Privacy Policy</a></li>
                    <li><a href="/terms" class="hover:text-white">Terms of Use</a></li>
                    <li><a href="/contact" class="hover:text-white">Contact</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} {{ \App\Models\SiteSetting::getValue('site_name', 'DarasaHub') }}. Independent educational directory; not affiliated with KNEC or KICD.
        </div>
    </footer>
</body>
</html>
