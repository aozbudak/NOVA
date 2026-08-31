<button
    type="button"
    data-theme-toggle
    aria-label="Toggle dark mode"
    {{ $attributes->merge(['class' => 'p-1 text-foreground']) }}
>
    <span class="dark:hidden">
        <x-icon name="moon" />
    </span>
    <span class="hidden dark:inline">
        <x-icon name="sun" />
    </span>
</button>
