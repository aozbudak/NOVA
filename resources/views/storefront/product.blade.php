@extends('layouts.storefront')

@section('title', $product['name'])
@section('description', $product['description'])

@section('content')
    <div class="mx-auto grid max-w-[1600px] gap-10 px-4 pt-6 pb-24 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:pt-10 lg:pb-0">
        <x-product-gallery :images="$product['images']" :name="$product['name']" />

        <div class="lg:sticky lg:top-24 lg:self-start lg:py-4">
            <p class="text-[11px] tracking-nav uppercase text-muted-foreground">NOVA</p>
            <h1 class="mt-2 font-serif text-3xl md:text-4xl">{{ $product['name'] }}</h1>
            <p class="mt-4 text-sm">
                @if ($product['oldPrice'])
                    <span class="text-muted-foreground line-through">{{ Number::currency($product['oldPrice'], in: 'EUR') }}</span>
                @endif
                {{ Number::currency($product['price'], in: 'EUR') }}
            </p>

            <form id="add-to-cart" class="mt-8 flex flex-col gap-8" data-add-to-cart>
                @csrf
                <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                <input type="hidden" name="quantity" value="1">

                <x-color-selector :colors="$product['colors']" />
                <x-size-selector :sizes="$product['sizes']" />

                <x-button type="submit" class="w-full">Add to bag</x-button>
            </form>

            <form method="post" action="{{ route('wishlist.store') }}" class="mt-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                <button type="button" class="inline-flex items-center gap-2 text-[11px] tracking-nav uppercase" data-wishlist-toggle data-product-id="{{ $product['id'] }}" aria-pressed="{{ in_array($product['id'], $wishlistIds, true) ? 'true' : 'false' }}">
                    <x-icon name="heart" :filled="in_array($product['id'], $wishlistIds, true)" />
                    Add to wishlist
                </button>
            </form>

            <div class="mt-10">
                <x-accordion title="Product details">
                    {{ $product['description'] }}
                </x-accordion>
                <x-accordion title="Material">
                    {{ $product['material'] }} Dry clean only. Made in Europe.
                </x-accordion>
                <x-accordion title="Shipping">
                    Complimentary shipping on orders over €100. Standard delivery 3–5 working days.
                </x-accordion>
                <x-accordion title="Returns">
                    Free returns within 30 days. Items must be unworn, with tags attached.
                </x-accordion>
            </div>
        </div>
    </div>

    <div class="fixed inset-x-0 bottom-0 z-30 border-t border-border bg-background p-3 lg:hidden">
        <x-button type="submit" form="add-to-cart" class="w-full">Add to bag</x-button>
    </div>

    @if ($related->isNotEmpty())
        <section class="mx-auto mt-24 max-w-[1600px] px-4 pb-16 md:px-8">
            <h2 class="mb-8 font-serif text-3xl">You may also like</h2>
            <x-product-grid :products="$related" :wishlist-ids="$wishlistIds" />
        </section>
    @endif
@endsection
