@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">{{ $h1 }}</h1>

    @include('partials.editorial-intro')

    @if($termsData->isNotEmpty())
        <section aria-labelledby="terms-heading" class="space-y-4">
            <h2 id="terms-heading" class="text-lg font-bold text-slate-900">Terms &amp; Assessments</h2>
            <div class="grid gap-5 md:grid-cols-3">
                @foreach($termsData as $row)
                    @php($term = $row['term'])
                    <div class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col justify-between gap-4">
                        <div>
                            <div class="mb-3">
                                @include('partials.status-check-button', [
                                    'label' => $term->name.' Exams',
                                    'checked' => $row['available'],
                                    'count' => $row['available'] ? $row['paper_count'] : null,
                                    'href' => $row['available'] ? "/{$grade->slug}/{$area->slug}/{$term->slug}" : null,
                                    'unavailableText' => "No verified {$grade->name} {$area->name} {$term->name} assessments yet",
                                ])
                            </div>

                            <div class="flex flex-wrap gap-2 pt-3 border-t border-slate-100">
                                @foreach($row['exam_types'] as $etRow)
                                    @php($examType = $etRow['exam_type'])
                                    @include('partials.status-check-button', [
                                        'label' => $examType->short_label ?: $examType->name,
                                        'checked' => $etRow['available'],
                                        'count' => $etRow['available'] ? $etRow['count'] : null,
                                        'href' => $etRow['available'] ? "/{$grade->slug}/{$area->slug}/{$term->slug}#{$examType->slug}" : null,
                                        'unavailableText' => "No verified {$examType->name} in {$term->name} yet",
                                    ])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @include('partials.faq-section')
</div>
@endsection
