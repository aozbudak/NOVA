<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.inventory') }}</h2>
    <div class="grid gap-4">
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.settings.low_stock_threshold') }}
            <input name="low_stock_threshold" type="number" min="0" value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex items-center gap-2 text-[13px] text-foreground">
            <input type="checkbox" name="allow_negative_stock" value="1" @checked(old('allow_negative_stock', $settings['allow_negative_stock'])) class="size-3.5 rounded-sm border-input">
            {{ __('admin.settings.allow_negative_stock') }}
        </label>
    </div>
</section>
