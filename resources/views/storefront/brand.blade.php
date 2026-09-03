@extends('layouts.storefront')

@section('title', $brand->name)

@section('content')
    <div class="mx-auto max-w-[1600px] px-4 pt-10 md:px-8 md:pt-14">
        <p class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.nav.brands') }} / {{ $brand->name }}</p>
        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-serif text-4xl md:text-5xl">{{ $brand->name }}</h1>
                @if ($brand->description)
                    <p class="mt-3 max-w-2xl text-sm text-muted-foreground">{{ $brand->description }}</p>
                @endif
                <p class="mt-2 text-xs tracking-wide text-muted-foreground">{{ __('storefront.listing.products_count', ['count' => $products->total()]) }}</p>
            </div>
            <div class="flex items-center gap-4">
                <form method="get" action="{{ route('brands.show', $brand->slug) }}" class="flex items-center gap-2">
                    @foreach (['size', 'color', 'price', 'collection', 'availability'] as $key)
                        @if (filled($filters[$key] ?? null))
                            <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">
                        @endif
                    @endforeach
                    <label class="sr-only" for="brand-sort">{{ __('storefront.listing.sort') }}</label>
                    <select id="brand-sort" name="sort" onchange="this.form.submit()" class="bg-transparent py-1 text-[11px] tracking-label uppercase outline-none">
                        <option value="recommended" @selected(($filters['sort'] ?? 'recommended') === 'recommended')>{{ __('storefront.listing.recommended') }}</option>
                        <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>{{ __('storefront.listing.newest') }}</option>
                        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('storefront.listing.price_asc') }}</option>
                        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('storefront.listing.price_desc') }}</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="mt-10 grid gap-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <aside class="hidden lg:block">
                <form method="get" action="{{ route('brands.show', $brand->slug) }}" class="flex flex-col gap-8">
                    @if ($filters['sort'] ?? null)
                        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
                    @endif
                    <fieldset>
                        <legend class="text-[11px] tracking-nav uppercase">{{ __('storefront.listing.size') }}</legend>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (['XS', 'S', 'M', 'L', 'XL', '40', '41', '42', '43', '44'] as $size)
                                <label>
                                    <input type="radio" name="size" value="{{ $size }}" class="peer sr-only" @checked(($filters['size'] ?? null) === $size) onchange="this.form.submit()">
                                    <span class="inline-flex min-w-9 cursor-pointer items-center justify-center border border-border px-2 py-1.5 text-xs peer-checked:border-foreground peer-checked:bg-foreground peer-checked:text-background">{{ $size }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend class="text-[11px] tracking-nav uppercase">{{ __('storefront.listing.availability') }}</legend>
                        <label class="mt-3 flex items-center gap-2 text-sm text-muted-foreground">
                            <input type="checkbox" name="availability" value="in-stock" @checked(($filters['availability'] ?? null) === 'in-stock') onchange="this.form.submit()">
                            {{ __('storefront.listing.in_stock') }}
                        </label>
                    </fieldset>
                </form>
            </aside>
            <div>
                @if ($products->isEmpty())
                    <x-empty-state :title="__('storefront.listing.no_products')">
                        {{ __('storefront.listing.no_products_body') }}
                    </x-empty-state>
                @else
                    <x-product-grid :products="$products" :wishlist-ids="$wishlistIds" />
                    <div class="mt-10">{{ $products->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
