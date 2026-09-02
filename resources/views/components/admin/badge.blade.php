@props(['status'])

@php
    $tone = match ($status) {
        'in_stock' => 'text-success',
        'low_stock' => 'text-warning',
        'out_of_stock' => 'text-destructive',
        default => 'text-muted-foreground',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-sm bg-muted px-1.5 py-0.5 text-[10px] font-medium tracking-wide uppercase '.$tone]) }}>
    {{ __('admin.stock.'.$status) }}
</span>
