@props([
    'name' => null,
])

<textarea
    @if ($name)
        name="{{ $name }}"
        id="{{ $name }}"
    @endif
    {{ $attributes->merge([
        'class' => 'w-full rounded-md border border-input bg-background px-3 py-2 text-[13px] text-foreground outline-none placeholder:text-muted-foreground aria-[invalid]:border-destructive',
    ])->merge($name && $errors->has($name) ? ['aria-invalid' => 'true'] : []) }}
>{{ $slot }}</textarea>
