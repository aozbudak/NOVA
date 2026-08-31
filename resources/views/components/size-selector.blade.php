@props([
    'sizes',
    'name' => 'size',
])

<fieldset {{ $attributes }}>
    <legend class="text-[11px] font-medium tracking-nav uppercase">Size</legend>
    <div class="mt-3 flex flex-wrap gap-2">
        @foreach ($sizes as $size)
            <label class="relative">
                <input
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $size['code'] }}"
                    class="peer sr-only"
                    @disabled(! $size['in_stock'])
                    @checked($loop->first && $size['in_stock'])
                >
                <span class="inline-flex min-w-11 items-center justify-center border border-border px-3 py-2 text-xs tracking-wide {{ $size['in_stock'] ? 'cursor-pointer peer-checked:border-foreground peer-checked:bg-foreground peer-checked:text-background' : 'cursor-not-allowed text-muted-foreground line-through opacity-50' }}">
                    {{ $size['code'] }}
                </span>
            </label>
        @endforeach
    </div>
    <a href="{{ route('pages.show', 'size-guide') }}" class="mt-3 inline-block text-[10px] tracking-label uppercase text-muted-foreground underline-offset-4 hover:underline">Size guide</a>
</fieldset>
