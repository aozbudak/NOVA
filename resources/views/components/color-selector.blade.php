@props([
    'colors',
    'name' => 'color',
])

<fieldset {{ $attributes }}>
    <legend class="text-[11px] font-medium tracking-nav uppercase">{{ __('storefront.product.color') }}</legend>
    <div class="mt-3 flex flex-wrap items-center gap-3">
        @foreach ($colors as $color)
            <label class="relative flex cursor-pointer items-center gap-2">
                <input type="radio" name="{{ $name }}" value="{{ $color['name'] }}" class="peer sr-only" @checked($loop->first)>
                <span class="size-5 rounded-full border border-border ring-offset-background peer-checked:ring-1 peer-checked:ring-foreground peer-checked:ring-offset-2" style="background-color: {{ $color['hex'] }}"></span>
                <span class="text-xs text-muted-foreground peer-checked:text-foreground">{{ $color['name'] }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
