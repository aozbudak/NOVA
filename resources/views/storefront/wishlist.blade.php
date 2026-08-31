@extends('layouts.storefront')

@section('title', __('storefront.wishlist.title'))

@section('content')
    <div class="mx-auto max-w-[1600px] px-4 pt-10 md:px-8 md:pt-14">
        <h1 class="font-serif text-4xl md:text-5xl">{{ __('storefront.wishlist.title') }}</h1>
        <div class="mt-12">
            @if ($products->isEmpty())
                <x-empty-state :title="__('storefront.wishlist.empty')">
                    {{ __('storefront.wishlist.empty_body') }}
                    <x-slot:action>
                        <x-button href="{{ route('shop.show', 'new-in') }}">{{ __('storefront.wishlist.explore') }}</x-button>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-product-grid :products="$products" :wishlist-ids="$wishlistIds" />
            @endif
        </div>
    </div>
@endsection
