@extends('layouts.storefront')

@section('title', 'My Wishlist')

@section('content')
    <div class="mx-auto max-w-[1600px] px-4 pt-10 md:px-8 md:pt-14">
        <h1 class="font-serif text-4xl md:text-5xl">My wishlist</h1>
        <div class="mt-12">
            @if ($products->isEmpty())
                <x-empty-state title="Your wishlist is empty">
                    Discover the latest NOVA collection.
                    <x-slot:action>
                        <x-button href="{{ route('shop.show', 'new-in') }}">Explore collection</x-button>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-product-grid :products="$products" :wishlist-ids="$wishlistIds" />
            @endif
        </div>
    </div>
@endsection
