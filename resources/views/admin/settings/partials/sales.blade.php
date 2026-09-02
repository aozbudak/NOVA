<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.sales') }}</h2>
    <div class="grid gap-4">
        <x-admin.field :label="__('admin.settings.default_discount')" name="default_discount">
            <x-admin.input name="default_discount" type="number" min="0" max="100" value="{{ old('default_discount', $settings['default_discount']) }}" />
        </x-admin.field>
        <fieldset class="flex flex-col gap-2">
            <legend class="text-[12px] text-muted-foreground">{{ __('admin.settings.payment_settings') }}</legend>
            <label class="flex items-center gap-2 text-[13px] text-foreground">
                <input type="checkbox" name="payment_cash" value="1" @checked(old('payment_cash', $settings['payment_cash'])) class="size-3.5 rounded-sm border-input">
                {{ __('admin.pos.cash') }}
            </label>
            <label class="flex items-center gap-2 text-[13px] text-foreground">
                <input type="checkbox" name="payment_card" value="1" @checked(old('payment_card', $settings['payment_card'])) class="size-3.5 rounded-sm border-input">
                {{ __('admin.pos.card') }}
            </label>
        </fieldset>
        <x-admin.field :label="__('admin.settings.receipt_footer')" name="receipt_footer">
            <x-admin.input name="receipt_footer" value="{{ old('receipt_footer', $settings['receipt_footer']) }}" />
        </x-admin.field>
    </div>
</section>
