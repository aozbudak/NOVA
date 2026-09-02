@props([
    'name' => null,
])

<select
    @if ($name)
        name="{{ $name }}"
        id="{{ $name }}"
    @endif
    {{ $attributes->merge([
        'class' => 'h-9 w-full rounded-xl border border-input bg-background px-3 text-[13px] text-foreground outline-none aria-[invalid]:border-destructive',
    ])->merge($name && $errors->has($name) ? ['aria-invalid' => 'true'] : []) }}
>
    {{ $slot }}
</select>
