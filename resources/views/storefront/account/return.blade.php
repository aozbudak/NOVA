@extends('layouts.storefront')

@section('title', $returnRequest['number'])

@section('content')
    <x-account.shell>
        <x-account.card class="overflow-hidden">
            <div class="flex flex-col gap-4 px-5 py-5 md:flex-row md:items-end md:justify-between">
                <div class="min-w-0">
                    <a href="{{ route('account.returns') }}" class="inline-flex items-center gap-2 text-[12px] text-muted-foreground transition-colors hover:text-foreground">
                        <x-icon name="chevron-left" size="size-3.5" />
                        {{ __('storefront.account.back_to_returns') }}
                    </a>
                    <h1 class="mt-2 font-serif text-2xl tracking-tight">{{ $returnRequest['number'] }}</h1>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $returnRequest['date'] }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-[11px]">{{ $returnRequest['status_label'] }}</span>
            </div>
        </x-account.card>

        <x-account.card class="overflow-hidden">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-sm font-medium">{{ __('storefront.account.products') }}</h2>
            </div>
            <ul class="divide-y divide-border">
                @foreach ($returnRequest['items'] as $item)
                    <li class="grid grid-cols-[4.5rem_minmax(0,1fr)_auto] gap-4 px-5 py-4">
                        <span class="block aspect-[4/5] overflow-hidden rounded-xl bg-muted">
                            @if (filled($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="size-full object-cover">
                            @else
                                <span class="flex size-full items-center justify-center text-muted-foreground">
                                    <x-icon name="bag" size="size-5" />
                                </span>
                            @endif
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ $item['name'] }}</p>
                            @if (filled($item['brand']))
                                <p class="mt-0.5 text-[12px] text-muted-foreground">{{ $item['brand'] }}</p>
                            @endif
                            <p class="mt-1 text-[12px] text-muted-foreground">{{ $item['variant'] }}</p>
                        </div>
                        <p class="text-sm tabular-nums">{{ Number::currency($item['total'], in: $returnRequest['currency']) }}</p>
                    </li>
                @endforeach
            </ul>
        </x-account.card>

        <x-account.card class="overflow-hidden">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-sm font-medium">{{ __('storefront.account.return_detail') }}</h2>
            </div>
            <dl class="flex flex-col gap-2 px-5 py-4 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('storefront.account.order_id') }}</dt>
                    <dd>
                        <a href="{{ route('account.orders.show', $returnRequest['order_number']) }}" class="hover:underline">{{ $returnRequest['order_number'] }}</a>
                    </dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('storefront.account.return_reason') }}</dt>
                    <dd>{{ $returnRequest['reason_label'] }}</dd>
                </div>
                @if (filled($returnRequest['notes']))
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">{{ __('storefront.account.customer_notes') }}</dt>
                        <dd class="max-w-sm text-right">{{ $returnRequest['notes'] }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('storefront.account.admin_action') }}</dt>
                    <dd>{{ $returnRequest['admin_action'] }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-muted-foreground">{{ __('storefront.account.admin_result') }}</dt>
                    <dd>{{ $returnRequest['result'] }}</dd>
                </div>
                @if (($returnRequest['status'] ?? '') === 'rejected' && filled($returnRequest['admin_notes']))
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">{{ __('storefront.account.rejection_reason') }}</dt>
                        <dd class="max-w-sm text-right">{{ $returnRequest['admin_notes'] }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-3 border-t border-border pt-2 font-medium">
                    <dt>{{ __('storefront.account.order_total') }}</dt>
                    <dd class="tabular-nums">{{ Number::currency($returnRequest['amount'], in: $returnRequest['currency']) }}</dd>
                </div>
            </dl>
        </x-account.card>
    </x-account.shell>
@endsection
