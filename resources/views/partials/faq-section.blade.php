@if(!empty($content) && $content->isApprovedForPublic() && count($content->visible_faqs) > 0)
<section class="mt-12 bg-white border border-slate-200 rounded-xl p-6" aria-labelledby="faq-heading">
    <h2 id="faq-heading" class="text-lg font-bold text-slate-900 mb-4">Frequently Asked Questions</h2>
    <div class="divide-y divide-slate-200">
        @foreach($content->visible_faqs as $faq)
            <details class="py-3 group">
                <summary class="flex items-center justify-between cursor-pointer font-medium text-slate-900 hover:text-brand-700">
                    <span>{{ $faq['question'] }}</span>
                    <span class="ml-4 text-slate-400 group-open:rotate-180 transition-transform">&#9662;</span>
                </summary>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $faq['answer'] }}</p>
            </details>
        @endforeach
    </div>
</section>
@endif
