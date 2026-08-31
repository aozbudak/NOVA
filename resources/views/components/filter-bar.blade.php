@props([
    'department',
    'category' => null,
    'filters',
])

@php
    $action = route('shop.show', array_filter(['department' => $department, 'category' => $category]));
@endphp

<form method="get" action="{{ $action }}" data-filter-form class="flex flex-col gap-8">
    <fieldset>
        <legend class="text-[11px] tracking-nav uppercase">Category</legend>
        <div class="mt-3 flex flex-col gap-2 text-sm">
            @foreach (['dresses' => 'Dresses', 'blazers' => 'Blazers', 'knitwear' => 'Knitwear', 'outerwear' => 'Outerwear', 'trousers' => 'Trousers', 'denim' => 'Denim', 'accessories' => 'Accessories'] as $value => $label)
                <label class="flex items-center gap-2">
                    <input type="radio" name="category_nav" value="{{ $value }}" class="sr-only peer" @checked($category === $value)>
                    <a href="{{ route('shop.show', ['department' => $department, 'category' => $value]) }}" class="hover:text-muted-foreground {{ $category === $value ? 'text-foreground' : 'text-muted-foreground' }}">{{ $label }}</a>
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset>
        <legend class="text-[11px] tracking-nav uppercase">Size</legend>
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach (['XS', 'S', 'M', 'L', 'XL'] as $size)
                <label>
                    <input type="radio" name="size" value="{{ $size }}" class="peer sr-only" @checked(($filters['size'] ?? null) === $size) onchange="this.form.submit()">
                    <span class="inline-flex min-w-9 cursor-pointer items-center justify-center border border-border px-2 py-1.5 text-xs peer-checked:border-foreground peer-checked:bg-foreground peer-checked:text-background">{{ $size }}</span>
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset>
        <legend class="text-[11px] tracking-nav uppercase">Color</legend>
        <div class="mt-3 flex flex-col gap-2 text-sm text-muted-foreground">
            @foreach (['Black', 'Ivory', 'Camel', 'Navy', 'Charcoal'] as $color)
                <label class="flex items-center gap-2">
                    <input type="radio" name="color" value="{{ $color }}" @checked(($filters['color'] ?? null) === $color) onchange="this.form.submit()">
                    {{ $color }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset>
        <legend class="text-[11px] tracking-nav uppercase">Price</legend>
        <div class="mt-3 flex flex-col gap-2 text-sm text-muted-foreground">
            @foreach (['under-150' => 'Under €150', '150-250' => '€150 – €250', 'over-250' => 'Over €250'] as $value => $label)
                <label class="flex items-center gap-2">
                    <input type="radio" name="price" value="{{ $value }}" @checked(($filters['price'] ?? null) === $value) onchange="this.form.submit()">
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset>
        <legend class="text-[11px] tracking-nav uppercase">Collection</legend>
        <div class="mt-3 flex flex-col gap-2 text-sm text-muted-foreground">
            @foreach (['Autumn Winter 2026', 'Essentials'] as $collection)
                <label class="flex items-center gap-2">
                    <input type="radio" name="collection" value="{{ $collection }}" @checked(($filters['collection'] ?? null) === $collection) onchange="this.form.submit()">
                    {{ $collection }}
                </label>
            @endforeach
        </div>
    </fieldset>

    <fieldset>
        <legend class="text-[11px] tracking-nav uppercase">Availability</legend>
        <label class="mt-3 flex items-center gap-2 text-sm text-muted-foreground">
            <input type="checkbox" name="availability" value="in-stock" @checked(($filters['availability'] ?? null) === 'in-stock') onchange="this.form.submit()">
            In stock
        </label>
    </fieldset>

    @if ($filters['sort'] ?? null)
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
    @endif
</form>
