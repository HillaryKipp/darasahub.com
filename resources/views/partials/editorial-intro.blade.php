@if(!empty($content) && $content->isApprovedForPublic() && !empty(trim((string) $content->intro)))
<div class="bg-white border border-slate-200 rounded-xl p-5 sm:p-6 mb-8 prose-sm max-w-none text-slate-700 leading-relaxed space-y-3">
    {!! nl2br(e($content->intro)) !!}
</div>
@endif
