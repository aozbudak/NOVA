@props([
    'label',
    'name' => null,
    'help' => null,
    'required' => false,
])

<div {{ $attributes->class('flex flex-col gap-1.5') }}>
    <label @if ($name) for="{{ $name }}" @endif class="text-[12px] text-muted-foreground">
        {{ $label }}
        @if ($required)
            <span class="text-destructive" aria-hidden="true">*</span>
            <span class="sr-only">{{ __('admin.common.required') }}</span>
        @endif
    </label>
    {{ $slot }}
    @if ($help)
        <p class="text-[11px] text-muted-foreground">{{ $help }}</p>
    @endif
    @if ($name)
        @error($name)
            <p class="text-[12px] text-destructive">{{ $message }}</p>
        @enderror
    @endif
</div>
