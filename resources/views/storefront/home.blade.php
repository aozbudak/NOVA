@extends('layouts.storefront')

@section('title', __('storefront.home.title'))
@section('description', __('storefront.home.description'))

@section('content')
    <x-hero-section />
    <x-collection-section :campaigns="$campaigns" />

    <section class="mx-auto mt-20 max-w-[1600px] px-4 md:mt-28 md:px-8">
        <div class="mb-10 flex items-end justify-between">
            <h2 class="font-serif text-3xl md:text-4xl">{{ __('storefront.home.new_arrivals') }}</h2>
            <a href="{{ route('shop.show', 'new-in') }}" class="hidden items-center gap-2 text-[11px] tracking-nav uppercase md:inline-flex">
                {{ __('storefront.home.view_all') }}
                <x-icon name="arrow-right" size="size-4" />
            </a>
        </div>
        <x-product-grid :products="$products" :wishlist-ids="$wishlistIds" />
    </section>
@endsection
