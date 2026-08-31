@extends('layouts.storefront')

@section('title', 'Autumn Winter 2026')
@section('description', 'NOVA Autumn Winter 2026. Contemporary tailoring, considered knits, and a quieter kind of luxury.')

@section('content')
    <x-hero-section />
    <x-collection-section :campaigns="$campaigns" />

    <section class="mx-auto mt-20 max-w-[1600px] px-4 md:mt-28 md:px-8">
        <div class="mb-10 flex items-end justify-between">
            <h2 class="font-serif text-3xl md:text-4xl">New arrivals</h2>
            <a href="{{ route('shop.show', 'new-in') }}" class="hidden items-center gap-2 text-[11px] tracking-nav uppercase md:inline-flex">
                View all
                <x-icon name="arrow-right" size="size-4" />
            </a>
        </div>
        <x-product-grid :products="$products" :wishlist-ids="$wishlistIds" />
    </section>
@endsection
