@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-4">{{ $h1 }}</h1>

    @include('partials.editorial-intro')

    @if($papers->isNotEmpty())
        <section aria-labelledby="papers-list-heading">
            <h2 id="papers-list-heading" class="text-lg font-bold text-slate-900 mb-4">
                Available Papers ({{ $papers->total() }})
            </h2>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($papers as $paper)
                    @include('partials.paper-card', ['paper' => $paper])
                @endforeach
            </div>
            <div class="mt-8">
                {{ $papers->links() }}
            </div>
        </section>
    @endif

    <x-internal-links :groups="$internalLinks ?? []" />

    @include('partials.faq-section')
</div>
@endsection
