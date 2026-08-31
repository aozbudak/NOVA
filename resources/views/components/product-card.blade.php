@props([
    'product',
    'wishlistIds' => [],
])

@php
    $inWishlist = in_array($product['id'], $wishlistIds, true);
    $quickSize = collect($product['sizes'])->firstWhere('in_stock', true)['code'] ?? 'M';
@endphp

<article {{ $attributes->merge(['class' => 'group']) }}>
    <div class="relative">
        <a href="{{ route('product.show', $product['slug']) }}" class="block overflow-hidden bg-muted">
            <img
                src="{{ $product['images'][0] }}"
                alt="{{ $product['name'] }}"
                width="700"
                height="875"
                class="aspect-[4/5] w-full object-cover transition-opacity duration-300 group-hover:opacity-0"
                loading="lazy"
                sizes="(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 50vw"
            >
            @if (isset($product['images'][1]))
                <img
                    src="{{ $product['images'][1] }}"
                    alt=""
                    width="700"
                    height="875"
                    class="absolute inset-0 aspect-[4/5] w-full object-cover opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                    loading="lazy"
                >
            @endif
        </a>
        <x-wishlist-button :product-id="$product['id']" :active="$inWishlist" class="absolute top-3 right-3" />
        <button
            type="button"
            class="absolute inset-x-3 bottom-3 hidden bg-background/95 py-2.5 text-[10px] tracking-nav uppercase opacity-0 transition-opacity duration-150 group-hover:opacity-100 md:block"
            data-quick-add
            data-product-id="{{ $product['id'] }}"
            data-size="{{ $quickSize }}"
        >
            Quick add
        </button>
    </div>
    <div class="mt-3 flex flex-col gap-1">
        <a href="{{ route('product.show', $product['slug']) }}" class="text-sm">{{ $product['name'] }}</a>
        <p class="text-sm">
            @if ($product['oldPrice'])
                <span class="text-muted-foreground line-through">{{ Number::currency($product['oldPrice'], in: 'EUR') }}</span>
            @endif
            {{ Number::currency($product['price'], in: 'EUR') }}
        </p>
        <p class="text-xs text-muted-foreground">{{ $product['colors'][0]['name'] }}</p>
    </div>
</article>
