<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.inventory') }}</h2>
    <div class="grid gap-4">
        <x-admin.field :label="__('admin.settings.low_stock_threshold')" name="low_stock_threshold">
            <x-admin.input name="low_stock_threshold" type="number" min="0" value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}" />
        </x-admin.field>
        <label class="flex items-center gap-2 text-[13px] text-foreground">
            <input type="checkbox" name="allow_negative_stock" value="1" @checked(old('allow_negative_stock', $settings['allow_negative_stock'])) class="size-3.5 rounded-sm border-input">
            {{ __('admin.settings.allow_negative_stock') }}
        </label>
    </div>
</section>
