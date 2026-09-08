@props([
    'name',
    'options' => [],
    'selected' => [],
    'placeholder' => null,
])

@php
    $selected = collect($selected)->map(fn (mixed $id): string => (string) $id)->all();
    $placeholder ??= __('admin.common.select');
    $byId = collect($options)->keyBy(fn (array $option): string => (string) $option['id']);
@endphp

<div {{ $attributes->class('flex flex-col gap-1.5') }} data-picker>
    <select
        data-picker-source
        id="{{ $name }}"
        class="h-9 w-full rounded-xl border border-input bg-background px-3 text-[13px] text-foreground outline-none aria-[invalid]:border-destructive"
        @if ($errors->has($name)) aria-invalid="true" @endif
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $option)
            <option
                value="{{ $option['id'] }}"
                data-label="{{ $option['label'] }}"
                @disabled(in_array((string) $option['id'], $selected, true))
            >{{ $option['label'] }}</option>
        @endforeach
    </select>
    <div data-picker-chips class="flex flex-wrap gap-1.5 empty:hidden">
        @foreach ($selected as $id)
            @continue(! $byId->has($id))
            <span data-picker-chip class="inline-flex items-center gap-1 rounded-lg bg-muted px-2 py-1 text-[12px] text-foreground">
                <input type="hidden" name="{{ $name }}[]" value="{{ $id }}">
                <span data-picker-label>{{ $byId[$id]['label'] }}</span>
                <button type="button" data-picker-remove class="rounded-md p-0.5 text-muted-foreground hover:text-foreground" aria-label="{{ __('admin.common.close') }}">
                    <x-icon name="x" size="size-3" />
                </button>
            </span>
        @endforeach
    </div>
    <template data-picker-template>
        <span data-picker-chip class="inline-flex items-center gap-1 rounded-lg bg-muted px-2 py-1 text-[12px] text-foreground">
            <input type="hidden" name="{{ $name }}[]" value="">
            <span data-picker-label></span>
            <button type="button" data-picker-remove class="rounded-md p-0.5 text-muted-foreground hover:text-foreground" aria-label="{{ __('admin.common.close') }}">
                <x-icon name="x" size="size-3" />
            </button>
        </span>
    </template>
</div>
