@props(['order'])

@php
    $tone = match ($order['status_key'] ?? '') {
        'delivered' => 'bg-success',
        'in_transit' => 'bg-warning',
        default => 'bg-muted-foreground',
    };
@endphp

<article {{ $attributes->merge(['class' => 'grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3 px-4 py-3 md:grid-cols-[auto_minmax(0,1.3fr)_1fr_1fr_auto] md:gap-4 md:px-5']) }}>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-muted">
        <x-icon name="bag" size="size-4" />
    </span>
    <div class="min-w-0">
        <p class="truncate text-sm font-medium">{{ __('storefront.account.order', ['id' => $order['id']]) }}</p>
        <p class="mt-0.5 text-[12px] text-muted-foreground md:hidden">{{ $order['date'] }}</p>
    </div>
    <p class="hidden text-sm text-muted-foreground md:block">{{ $order['date'] }}</p>
    <p class="hidden text-sm tabular-nums md:block">{{ Number::currency($order['total'], in: $order['currency'] ?? 'EUR') }}</p>
    <div class="flex flex-col items-end gap-1">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-[11px]">
            <span class="size-1.5 rounded-full {{ $tone }}"></span>
            {{ $order['status'] }}
        </span>
        <p class="text-[12px] tabular-nums md:hidden">{{ Number::currency($order['total'], in: $order['currency'] ?? 'EUR') }}</p>
    </div>
</article>
