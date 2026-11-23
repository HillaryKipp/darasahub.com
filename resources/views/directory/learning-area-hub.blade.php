@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">{{ $h1 }}</h1>

    @include('partials.editorial-intro')

    @if($termsData->isNotEmpty())
        <section aria-labelledby="terms-heading" class="grid gap-5 md:grid-cols-3">
            @foreach($termsData as $row)
                @php($term = $row['term'])
                <div class="bg-white border border-slate-200 rounded-xl p-5">
                    <h2 class="text-lg font-bold text-slate-900">
                        @if($row['term_hub_ready'])
                            <a href="/{{ $grade->slug }}/{{ $area->slug }}/{{ $term->slug }}" class="hover:text-brand-700 hover:underline">{{ $term->name }}</a>
                        @else
                            {{ $term->name }}
                        @endif
                    </h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach($row['ready_exam_types'] as $examType)
                            <li>
                                <a href="/{{ $grade->slug }}/{{ $area->slug }}/{{ $term->slug }}/{{ $examType->slug }}" class="text-brand-700 hover:underline font-medium">
                                    {{ $examType->name }} &rarr;
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </section>
    @endif

    @include('partials.faq-section')
</div>
@endsection
