@php
    $papersList = $papers ?? collect();
    $previewOpen = (bool) ($previewOpen ?? true);
    $showDescription = (bool) ($showDescription ?? false);
    $customTitleHref = $titleHref ?? null;
@endphp

@if($papersList->isNotEmpty())
    <div class="divide-y divide-slate-200 bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs">
        @foreach($papersList as $paper)
            @php
                $isMarkingScheme = $paper->file_type === \App\Models\Paper::FILE_TYPE_MARKING_SCHEME;
                $viewUrl = $paper->view_url;
                $titleUrl = $customTitleHref ?: $viewUrl;
                $previewUrl = $paper->preview_url;
                $isExternalTitle = $titleUrl && str_starts_with($titleUrl, 'http');
            @endphp
            <article x-data="{ showPreview: {{ $previewOpen ? 'true' : 'false' }} }" class="p-4 sm:p-5 hover:bg-slate-50/60 transition">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="min-w-0 flex-1 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                            @if($paper->year)
                                <span class="px-2 py-0.5 rounded-full bg-brand-50 text-brand-800 font-semibold">{{ $paper->year }}</span>
                            @endif
                            <span class="px-2 py-0.5 rounded-full {{ $isMarkingScheme ? 'bg-amber-50 text-amber-800' : 'bg-slate-100 text-slate-700' }} font-medium">
                                {{ $isMarkingScheme ? 'Marking Scheme' : 'Question Paper' }}
                            </span>
                            @if($paper->paper_number)
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $paper->paper_number }}</span>
                            @endif
                            @if($paper->set_label)
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $paper->set_label }}</span>
                            @endif
                            @if($paper->formatted_file_size)
                                <span class="text-slate-400">{{ $paper->formatted_file_size }}</span>
                            @endif
                        </div>

                        <h3 class="text-base font-bold text-slate-900 leading-snug">
                            @if($titleUrl)
                                <a
                                    href="{{ $titleUrl }}"
                                    @if($isExternalTitle) target="_blank" rel="noopener noreferrer" @endif
                                    class="text-brand-800 hover:text-brand-700 hover:underline decoration-brand-600/60 underline-offset-2"
                                >
                                    {{ $paper->title }}
                                </a>
                            @else
                                {{ $paper->title }}
                            @endif
                        </h3>

                        @if($showDescription && $paper->description)
                            <p class="text-sm text-slate-600 leading-relaxed max-w-3xl">
                                {{ trim(strip_tags($paper->description)) }}
                            </p>
                        @endif

                        @if($paper->relatedPaper && $paper->relatedPaper->isVerifiedReady() && $paper->relatedPaper->view_url)
                            <p class="text-xs text-slate-500">
                                Companion:
                                <a
                                    href="{{ $paper->relatedPaper->view_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="font-semibold text-brand-700 hover:underline"
                                >
                                    {{ $paper->relatedPaper->file_type === \App\Models\Paper::FILE_TYPE_MARKING_SCHEME ? 'Marking Scheme' : 'Question Paper' }}
                                </a>
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if($paper->hasWorkingResource())
                            <a
                                href="/download/{{ $paper->id }}"
                                rel="nofollow"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-brand-700 hover:bg-brand-800 text-white font-semibold text-xs sm:text-sm shadow-xs transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Download</span>
                            </a>
                        @endif

                        @if($previewUrl)
                            <button
                                type="button"
                                @click="showPreview = !showPreview"
                                class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50 text-slate-700 font-medium text-xs sm:text-sm transition"
                            >
                                <span x-show="!showPreview" @if($previewOpen) x-cloak @endif>Preview</span>
                                <span x-show="showPreview" @if(!$previewOpen) x-cloak @endif>Hide Preview</span>
                            </button>
                        @endif
                    </div>
                </div>

                @if($previewUrl)
                    <template x-if="showPreview">
                        <div class="mt-4 rounded-xl overflow-hidden border border-slate-200 bg-slate-100">
                            <iframe
                                src="{{ $previewUrl }}"
                                title="Preview for {{ $paper->title }}"
                                loading="lazy"
                                class="w-full h-[560px]"
                            ></iframe>
                        </div>
                    </template>
                @endif
            </article>
        @endforeach
    </div>
@endif
