@extends('layouts.storefront')

@section('title', $meta['title'])

@section('content')
    <article class="mx-auto max-w-2xl px-4 py-20 md:px-8">
        <p class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ $meta['kicker'] }}</p>
        <h1 class="mt-4 font-serif text-4xl md:text-5xl">{{ $meta['title'] }}</h1>
        <div class="mt-10 space-y-6 text-sm leading-relaxed text-muted-foreground">
            @include('storefront.pages.'.$page)
        </div>
    </article>
@endsection
