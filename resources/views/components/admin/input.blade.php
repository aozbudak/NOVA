@props([
    'name' => null,
])

@php
    $id = $attributes->get('id') ?? ($name ? str_replace(['[', ']'], '', $name) : null);
@endphp

<input
    @if ($name)
        name="{{ $name }}"
    @endif
    @if ($id)
        id="{{ $id }}"
    @endif
    {{ $attributes->except('id')->merge([
        'class' => 'h-9 w-full rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground aria-[invalid]:border-destructive',
    ])->merge($name && $errors->has($name) ? ['aria-invalid' => 'true'] : []) }}
>
