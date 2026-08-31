@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'required' => false,
    'autocomplete' => null,
])

<label class="flex flex-col gap-2">
    <span class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ $label }}</span>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ $value }}"
        @required($required)
        autocomplete="{{ $autocomplete }}"
        {{ $attributes->merge(['class' => 'border-b border-input bg-transparent py-2 text-sm outline-none transition-colors focus:border-foreground']) }}
    >
    @error($name)
        <span class="text-xs text-destructive">{{ $message }}</span>
    @enderror
</label>
