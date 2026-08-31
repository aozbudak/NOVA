@extends('layouts.storefront')

@section('title', __('storefront.search.title'))

@section('content')
    <div class="mx-auto max-w-[1600px] px-4 pt-10 md:px-8 md:pt-14">
        <p class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.search.title') }}</p>
        <h1 class="mt-3 font-serif text-4xl">{{ $query !== '' ? $query : __('storefront.search.prompt') }}</h1>
        @if ($query !== '')
            <p class="mt-2 text-xs text-muted-foreground">{{ __('storefront.listing.products_count', ['count' => $products->count()]) }}</p>
        @endif

        <form action="{{ route('search') }}" method="get" class="mt-8 max-w-xl border-b border-foreground pb-2">
            <label for="q" class="sr-only">{{ __('storefront.search.title') }}</label>
            <input id="q" name="q" value="{{ $query }}" type="search" class="w-full bg-transparent text-lg outline-none" placeholder="{{ __('storefront.search.placeholder') }}">
        </form>

        <div class="mt-12">
            @if ($query !== '' && $products->isEmpty())
                <x-empty-state :title="__('storefront.search.no_results')">
                    {{ __('storefront.search.no_results_body') }}
                    <x-slot:action>
                        <x-button href="{{ route('shop.show', 'new-in') }}" variant="outline">{{ __('storefront.search.explore') }}</x-button>
                    </x-slot:action>
                </x-empty-state>
            @elseif ($products->isNotEmpty())
                <x-product-grid :products="$products" :wishlist-ids="$wishlistIds" />
            @endif
        </div>
    </div>
@endsection
