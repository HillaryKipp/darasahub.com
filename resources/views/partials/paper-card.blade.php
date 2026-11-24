@php
    $isMarkingScheme = $paper->file_type === \App\Models\Paper::FILE_TYPE_MARKING_SCHEME;
    $targetUrl = $paper->bundle_url ?: $paper->url_path;
@endphp
<article class="bg-white border border-slate-200 rounded-xl p-4 hover:border-brand-600/50 transition flex items-center justify-between gap-3">
    <div class="min-w-0 flex-1 space-y-1.5">
        <div class="flex flex-wrap items-center gap-1.5 text-xs">
            @if($paper->year)
                <span class="px-2 py-0.5 rounded-full bg-brand-50 text-brand-800 font-semibold">{{ $paper->year }}</span>
            @endif
            <span class="px-2 py-0.5 rounded-full {{ $isMarkingScheme ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-700' }} font-medium">
                {{ $isMarkingScheme ? 'Marking Scheme' : 'Question Paper' }}
            </span>
            @if($paper->curriculum === \App\Models\Paper::CURRICULUM_LEGACY_844)
                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">8-4-4</span>
            @else
                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800">CBC</span>
            @endif
            @if($paper->formatted_file_size)
                <span class="text-slate-400">{{ $paper->formatted_file_size }}</span>
            @endif
        </div>

        <h3 class="font-semibold text-sm sm:text-base text-slate-900 leading-snug">
            @if($targetUrl)
                <a href="{{ $targetUrl }}" class="text-brand-800 hover:text-brand-700 hover:underline decoration-brand-600/60 underline-offset-2">
                    {{ $paper->title }}
                </a>
            @else
                {{ $paper->title }}
            @endif
        </h3>
    </div>

    @if($paper->hasWorkingResource())
        <a
            href="/download/{{ $paper->id }}"
            rel="nofollow"
            class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-700 hover:bg-brand-800 text-white font-semibold text-xs shadow-xs transition"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span>Download</span>
        </a>
    @endif
</article>
