@props(['status', 'group' => 'stock'])

@php
    $tone = match ($status) {
        'in_stock', 'active', 'completed', 'received', 'income', 'sale', 'opening' => 'text-success',
        'low_stock', 'partially_returned', 'open', 'adjustment', 'no_difference' => 'text-warning',
        'out_of_stock', 'cancelled', 'returned', 'refund', 'refunded', 'expense', 'closing', 'additional_payment' => 'text-destructive',
        default => 'text-muted-foreground',
    };

    $label = $group === 'stock'
        ? __('admin.stock.'.$status)
        : __('admin.status.'.$status);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-sm bg-muted px-1.5 py-0.5 text-[10px] font-medium tracking-wide uppercase '.$tone]) }}>
    {{ $label }}
</span>
