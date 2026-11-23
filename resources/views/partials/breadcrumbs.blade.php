@if(!empty($breadcrumbs))
<nav aria-label="Breadcrumb" class="mb-6 text-xs sm:text-sm text-slate-500">
    <ol class="flex flex-wrap items-center gap-1.5">
        @foreach($breadcrumbs as $index => $crumb)
            @if($index > 0)
                <li aria-hidden="true" class="text-slate-400">/</li>
            @endif
            <li>
                @if(!empty($crumb['url']))
                    <a href="{{ $crumb['url'] }}" class="hover:text-brand-700 hover:underline">{{ $crumb['label'] }}</a>
                @else
                    <span class="text-slate-800 font-medium" aria-current="page">{{ $crumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
