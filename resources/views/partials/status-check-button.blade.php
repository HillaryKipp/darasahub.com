@php
    $checked = (bool) ($checked ?? false);
    $active = (bool) ($active ?? false);
    $asButton = (bool) ($asButton ?? false);
    $href = $href ?? null;
    $count = $count ?? null;
    $unavailableText = $unavailableText ?? 'No assessments available yet';
    $alpineClick = $alpineClick ?? null;
    $alpineActive = $alpineActive ?? null;
@endphp

@if($checked)
    @if($asButton)
        <button
            type="button"
            @if($alpineClick) @click="{{ $alpineClick }}" @endif
            @if($alpineActive) :class="{{ $alpineActive }} ? 'bg-brand-700 text-white border-brand-700 shadow-sm' : 'bg-white text-brand-900 border-brand-600/40 hover:bg-brand-50 hover:border-brand-700'" @endif
            class="group inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border text-xs sm:text-sm font-semibold transition {{ $active ? 'bg-brand-700 text-white border-brand-700 shadow-sm' : 'bg-white text-brand-900 border-brand-600/40 hover:bg-brand-50 hover:border-brand-700' }}"
            data-status="checked"
        >
            <span>{{ $label }}</span>
            @if($count !== null)
                <span
                    class="px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $active ? 'bg-white/20 text-white' : 'bg-brand-50 text-brand-800' }}"
                    @if($alpineActive) :class="{{ $alpineActive }} ? 'bg-white/20 text-white' : 'bg-brand-50 text-brand-800'" @endif
                >{{ $count }}</span>
            @endif
        </button>
    @else
        <a
            href="{{ $href }}"
            class="group inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border text-xs sm:text-sm font-semibold transition {{ $active ? 'bg-brand-700 text-white border-brand-700 shadow-sm' : 'bg-white text-brand-900 border-brand-600/40 hover:bg-brand-700 hover:text-white hover:border-brand-700 shadow-xs' }}"
            data-status="checked"
        >
            <span>{{ $label }}</span>
            @if($count !== null)
                <span class="px-1.5 py-0.5 rounded-full text-[11px] font-medium {{ $active ? 'bg-white/20 text-white' : 'bg-brand-50 text-brand-800 group-hover:bg-white/20 group-hover:text-white' }}">
                    {{ $count }}
                </span>
            @endif
        </a>
    @endif
@else
    <span
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-slate-200 bg-slate-100/80 text-xs sm:text-sm font-medium text-slate-400 cursor-not-allowed select-none"
        aria-disabled="true"
        title="{{ $unavailableText }}"
        data-status="unchecked"
    >
        <span>{{ $label }}</span>
    </span>
@endif
