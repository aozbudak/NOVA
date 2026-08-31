@props(['filters', 'department', 'category' => null])

<form method="get" action="{{ route('shop.show', array_filter(['department' => $department, 'category' => $category])) }}" class="flex items-center gap-2">
    @foreach (['size', 'color', 'price', 'collection', 'availability'] as $key)
        @if (filled($filters[$key] ?? null))
            <input type="hidden" name="{{ $key }}" value="{{ $filters[$key] }}">
        @endif
    @endforeach
    <label class="sr-only" for="sort">{{ __('storefront.listing.sort') }}</label>
    <select id="sort" name="sort" onchange="this.form.submit()" class="bg-transparent py-1 text-[11px] tracking-label uppercase outline-none">
        <option value="recommended" @selected(($filters['sort'] ?? 'recommended') === 'recommended')>{{ __('storefront.listing.recommended') }}</option>
        <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>{{ __('storefront.listing.newest') }}</option>
        <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>{{ __('storefront.listing.price_asc') }}</option>
        <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>{{ __('storefront.listing.price_desc') }}</option>
    </select>
</form>
