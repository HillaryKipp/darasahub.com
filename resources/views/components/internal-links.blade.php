@props(['groups' => []])

@if(!empty($groups))
<section class="mt-12 grid gap-5 md:grid-cols-2" aria-label="Related curriculum links">
    @foreach($groups as $group)
        @if(!empty($group['links']))
            <div class="bg-white border border-slate-200 rounded-xl p-5 text-center">
                <h2 class="text-sm font-bold text-slate-900 mb-3">{{ $group['heading'] }}</h2>
                <ul class="space-y-2 text-sm">
                    @foreach($group['links'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-brand-700 hover:underline">{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endforeach
</section>
@endif
