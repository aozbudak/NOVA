@props(['order'])

@php
    $tone = match ($order['status_key'] ?? '') {
        'delivered' => 'bg-success',
        'in_transit' => 'bg-warning',
        'returned', 'cancelled' => 'bg-destructive',
        default => 'bg-muted-foreground',
    };
    $returnTone = match ($order['return_status'] ?? 'none') {
        'completed', 'approved' => 'bg-success',
        'pending' => 'bg-warning',
        'rejected' => 'bg-destructive',
        default => 'bg-muted-foreground',
    };
@endphp

<a href="{{ $order['href'] ?? route('account.orders.show', $order['number'] ?? $order['id']) }}" {{ $attributes->merge(['class' => 'grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3 px-4 py-3 transition-colors hover:bg-muted md:grid-cols-[auto_minmax(0,1.2fr)_0.9fr_0.7fr_0.9fr_0.8fr_auto] md:gap-4 md:px-5']) }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-muted">
        <x-icon name="bag" size="size-4" />
    </span>
    <div class="min-w-0">
        <p class="truncate text-sm font-medium">{{ $order['number'] ?? $order['id'] }}</p>
        <p class="mt-0.5 text-[12px] text-muted-foreground md:hidden">{{ $order['date'] }}</p>
    </div>
    <p class="hidden text-sm text-muted-foreground md:block">{{ $order['date'] }}</p>
    <p class="hidden text-sm text-muted-foreground md:block">{{ trans_choice('storefront.account.item_count', $order['item_count'] ?? 0, ['count' => $order['item_count'] ?? 0]) }}</p>
    <p class="hidden text-sm tabular-nums md:block">{{ Number::currency($order['total'], in: $order['currency'] ?? 'TRY') }}</p>
    <p class="hidden text-sm text-muted-foreground md:block">{{ $order['payment_label'] ?? '—' }}</p>
    <div class="flex flex-col items-end gap-1">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-[11px]">
            <span class="size-1.5 rounded-full {{ $tone }}"></span>
            {{ $order['status'] }}
        </span>
        @if (($order['return_status'] ?? 'none') !== 'none')
            <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-[11px]">
                <span class="size-1.5 rounded-full {{ $returnTone }}"></span>
                {{ $order['return_status_label'] }}
            </span>
        @endif
        <p class="text-[12px] tabular-nums md:hidden">{{ Number::currency($order['total'], in: $order['currency'] ?? 'TRY') }}</p>
    </div>
</a>
