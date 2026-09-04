@props([
    'name' => null,
])

@php
    $id = $attributes->get('id') ?? ($name ? str_replace(['[', ']'], '', $name) : null);
@endphp

<textarea
    @if ($name)
        name="{{ $name }}"
    @endif
    @if ($id)
        id="{{ $id }}"
    @endif
    {{ $attributes->except('id')->merge([
        'class' => 'w-full rounded-xl border border-input bg-background px-3 py-2 text-[13px] text-foreground outline-none placeholder:text-muted-foreground aria-[invalid]:border-destructive',
    ])->merge($name && $errors->has($name) ? ['aria-invalid' => 'true'] : []) }}
>{{ $slot }}</textarea>
