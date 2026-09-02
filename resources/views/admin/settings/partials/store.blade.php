<section class="rounded-md border border-border bg-card p-4">
    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.settings.categories.store') }}</h2>
    <div class="grid gap-4">
        <x-admin.field :label="__('admin.settings.store_info')" name="store_info">
            <x-admin.textarea name="store_info" rows="3">{{ old('store_info', $settings['store_info']) }}</x-admin.textarea>
        </x-admin.field>
        <x-admin.field :label="__('admin.settings.opening_hours')" name="opening_hours">
            <x-admin.input name="opening_hours" value="{{ old('opening_hours', $settings['opening_hours']) }}" />
        </x-admin.field>
        <x-admin.field :label="__('admin.settings.default_language')" name="default_language">
            <x-admin.select name="default_language">
                <option value="en" @selected(old('default_language', $settings['default_language']) === 'en')>English</option>
                <option value="tr" @selected(old('default_language', $settings['default_language']) === 'tr')>Türkçe</option>
            </x-admin.select>
        </x-admin.field>
    </div>
</section>
