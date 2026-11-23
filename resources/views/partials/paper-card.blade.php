<article class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5 hover:border-brand-600/50 transition flex flex-col justify-between gap-3">
    <div>
        <div class="flex flex-wrap items-center gap-2 text-xs mb-2">
            <span class="px-2 py-0.5 rounded bg-brand-50 text-brand-800 font-medium">{{ $paper->year }}</span>
            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                {{ $paper->file_type === \App\Models\Paper::FILE_TYPE_MARKING_SCHEME ? 'Marking Scheme' : 'Question Paper' }}
            </span>
            @if($paper->curriculum === \App\Models\Paper::CURRICULUM_LEGACY_844)
                <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-800">8-4-4 Legacy</span>
            @else
                <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800">CBC</span>
            @endif
        </div>
        <h3 class="font-semibold text-slate-900 leading-snug">
            @if($paper->url_path)
                <a href="{{ $paper->url_path }}" class="hover:text-brand-700 hover:underline">{{ $paper->title }}</a>
            @else
                {{ $paper->title }}
            @endif
        </h3>
        @if($paper->description)
            <p class="mt-1.5 text-sm text-slate-600 line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($paper->description), 130) }}</p>
        @endif
    </div>
    <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 text-xs text-slate-500">
        <span>
            @if($paper->source_name)
                Source: {{ $paper->source_name }}
            @endif
            @if($paper->formatted_file_size)
                &middot; {{ $paper->formatted_file_size }}
            @endif
        </span>
        @if($paper->url_path)
            <a href="{{ $paper->url_path }}" class="font-semibold text-brand-700 hover:underline">Open Paper &rarr;</a>
        @endif
    </div>
</article>
