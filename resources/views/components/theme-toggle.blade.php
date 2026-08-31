<button
    type="button"
    data-theme-toggle
    aria-label="{{ __('storefront.theme.toggle') }}"
    {{ $attributes->merge(['class' => 'p-1 text-current']) }}
>
    <span class="dark:hidden">
        <x-icon name="moon" />
    </span>
    <span class="hidden dark:inline">
        <x-icon name="sun" />
    </span>
</button>
