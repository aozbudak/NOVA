<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.general') }}</h2>
    <div class="grid gap-4">
        <x-admin.field :label="__('admin.settings.store_name')" name="store_name">
            <x-admin.input name="store_name" value="{{ old('store_name', $settings['store_name']) }}" />
        </x-admin.field>
        <x-admin.field :label="__('admin.settings.store_email')" name="store_email">
            <x-admin.input name="store_email" type="email" value="{{ old('store_email', $settings['store_email']) }}" />
        </x-admin.field>
        <x-admin.field :label="__('admin.settings.phone')" name="phone">
            <x-admin.input name="phone" value="{{ old('phone', $settings['phone']) }}" />
        </x-admin.field>
        <x-admin.field :label="__('admin.settings.address')" name="address">
            <x-admin.input name="address" value="{{ old('address', $settings['address']) }}" />
        </x-admin.field>
        <x-admin.field :label="__('admin.settings.currency')" name="currency">
            <x-admin.input name="currency" value="{{ old('currency', $settings['currency']) }}" />
        </x-admin.field>
    </div>
</section>
