@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @include('partials.breadcrumbs')

    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-6 text-center">Latest Verified Exam Papers</h1>

    @if($papers->isEmpty())
        <div class="bg-white border border-slate-200 rounded-xl p-6 text-sm text-slate-600 text-center">
            No verified papers are published yet.
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($papers as $paper)
                @include('partials.paper-card', ['paper' => $paper])
            @endforeach
        </div>
        <div class="mt-8 flex justify-center">
            {{ $papers->links() }}
        </div>
    @endif
</div>
@endsection
