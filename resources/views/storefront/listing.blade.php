@extends('layouts.storefront')

@section('title', $meta['title'])

@section('content')
    <div class="mx-auto max-w-[1600px] px-4 pt-10 md:px-8 md:pt-14">
        <p class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ $meta['breadcrumb'] }}</p>
        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-serif text-4xl md:text-5xl">{{ $meta['title'] }}</h1>
                <p class="mt-2 text-xs tracking-wide text-muted-foreground">{{ $products->count() }} products</p>
            </div>
            <div class="flex items-center gap-4">
                <button type="button" class="inline-flex items-center gap-2 text-[11px] tracking-nav uppercase lg:hidden" data-open="filters">
                    <x-icon name="filter" size="size-4" />
                    Filter
                </button>
                <x-sort-dropdown :filters="$filters" :department="$department" :category="$category" />
            </div>
        </div>

        <div class="mt-10 grid gap-12 lg:grid-cols-[16rem_minmax(0,1fr)]">
            <aside class="hidden lg:block">
                <x-filter-bar :department="$department" :category="$category" :filters="$filters" />
            </aside>
            <div>
                @if ($products->isEmpty())
                    <x-empty-state title="No products found">
                        Try another filter, or explore the latest NOVA collection.
                        <x-slot:action>
                            <x-button href="{{ route('shop.show', $department) }}" variant="outline">Clear filters</x-button>
                        </x-slot:action>
                    </x-empty-state>
                @else
                    <x-product-grid :products="$products" :wishlist-ids="$wishlistIds" />
                @endif
            </div>
        </div>
    </div>

    <x-filter-drawer>
        <x-filter-bar :department="$department" :category="$category" :filters="$filters" />
    </x-filter-drawer>
@endsection
