@extends('layouts.storefront')

@section('title', 'Search')

@section('content')
    <div class="mx-auto max-w-[1600px] px-4 pt-10 md:px-8 md:pt-14">
        <p class="text-[11px] tracking-nav uppercase text-muted-foreground">Search</p>
        <h1 class="mt-3 font-serif text-4xl">{{ $query !== '' ? $query : 'What are you looking for?' }}</h1>
        @if ($query !== '')
            <p class="mt-2 text-xs text-muted-foreground">{{ $products->count() }} products</p>
        @endif

        <form action="{{ route('search') }}" method="get" class="mt-8 max-w-xl border-b border-foreground pb-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" value="{{ $query }}" type="search" class="w-full bg-transparent text-lg outline-none" placeholder="Search">
        </form>

        <div class="mt-12">
            @if ($query !== '' && $products->isEmpty())
                <x-empty-state title="No results">
                    Try another search, or explore the latest NOVA collection.
                    <x-slot:action>
                        <x-button href="{{ route('shop.show', 'new-in') }}" variant="outline">Explore collection</x-button>
                    </x-slot:action>
                </x-empty-state>
            @elseif ($products->isNotEmpty())
                <x-product-grid :products="$products" :wishlist-ids="$wishlistIds" />
            @endif
        </div>
    </div>
@endsection
