@extends('layouts.storefront')

@section('title', $order['number'])

@section('content')
    <x-account.shell>
        <x-account.card class="overflow-hidden">
            <div class="flex flex-col gap-4 px-5 py-5 md:flex-row md:items-end md:justify-between">
                <div class="min-w-0">
                    <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 text-[12px] text-muted-foreground transition-colors hover:text-foreground">
                        <x-icon name="chevron-left" size="size-3.5" />
                        {{ __('storefront.account.back_to_orders') }}
                    </a>
                    <h1 class="mt-2 font-serif text-2xl tracking-tight">{{ $order['number'] }}</h1>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $order['date'] }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-[11px]">{{ $order['status'] }}</span>
                    @if (($order['return_status'] ?? 'none') !== 'none')
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-[11px]">{{ $order['return_status_label'] }}</span>
                    @endif
                </div>
            </div>
        </x-account.card>

        <x-account.card class="overflow-hidden">
            <div class="border-b border-border px-5 py-4">
                <h2 class="text-sm font-medium">{{ __('storefront.account.products') }}</h2>
            </div>
            <ul class="divide-y divide-border">
                @foreach ($order['items'] as $item)
                    <li class="grid grid-cols-[4.5rem_minmax(0,1fr)] gap-4 px-5 py-4 md:grid-cols-[5.5rem_minmax(0,1fr)_auto]">
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
                            <p class="mt-1 text-[12px] text-muted-foreground">
                                {{ collect([$item['variant'], __('storefront.account.quantity').': '.$item['quantity']])->filter()->implode(' · ') }}
                            </p>
                        </div>
                        <div class="col-span-2 flex items-end justify-between gap-4 text-sm md:col-span-1 md:flex-col md:items-end">
                            <p class="tabular-nums">{{ Number::currency($item['total'], in: $order['currency']) }}</p>
                            @if ($item['discount'] > 0)
                                <p class="text-[12px] text-muted-foreground">{{ __('storefront.account.discount') }} {{ Number::currency($item['discount'], in: $order['currency']) }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-account.card>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1.4fr)_22rem]">
            <x-account.card class="overflow-hidden">
                <div class="border-b border-border px-5 py-4">
                    <h2 class="text-sm font-medium">{{ __('storefront.account.order_detail') }}</h2>
                </div>
                <dl class="flex flex-col gap-2 px-5 py-4 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">{{ __('storefront.checkout.subtotal') }}</dt>
                        <dd class="tabular-nums">{{ Number::currency($order['subtotal'], in: $order['currency']) }}</dd>
                    </div>
                    @if ($order['discount'] > 0)
                        <div class="flex justify-between gap-3">
                            <dt class="text-muted-foreground">{{ __('storefront.account.discount') }}</dt>
                            <dd class="tabular-nums">{{ Number::currency($order['discount'], in: $order['currency']) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">{{ __('storefront.account.shipping') }}</dt>
                        <dd class="tabular-nums">{{ Number::currency($order['shipping'], in: $order['currency']) }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted-foreground">{{ __('storefront.account.payment_method') }}</dt>
                        <dd>{{ $order['payment_label'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-3 border-t border-border pt-2 font-medium">
                        <dt>{{ __('storefront.account.grand_total') }}</dt>
                        <dd class="tabular-nums">{{ Number::currency($order['total'], in: $order['currency']) }}</dd>
                    </div>
                </dl>
            </x-account.card>

            @if ($order['returnable'])
                @php $formOpen = $errors->any(); @endphp
                <div data-return-trigger @class(['hidden' => $formOpen])>
                    <x-button type="button" class="w-full" data-return-open>{{ __('storefront.account.request_return') }}</x-button>
                </div>
                <div data-return-panel @class(['hidden' => ! $formOpen])>
                    <form method="POST" action="{{ route('account.returns.store') }}" class="account-card overflow-hidden rounded-2xl border" data-return-form>
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order['number'] }}">
                        <div class="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                            <h2 class="text-sm font-medium">{{ __('storefront.account.request_return') }}</h2>
                            <button type="button" data-return-close class="text-[12px] text-muted-foreground transition-colors hover:text-foreground">
                                {{ __('storefront.account.cancel') }}
                            </button>
                        </div>
                        <div class="flex flex-col gap-5 p-5">
                            <label class="flex flex-col gap-2">
                                <span class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.account.return_reason') }}</span>
                                <select name="reason" required class="border-b border-input bg-transparent py-2 text-sm outline-none transition-colors focus:border-foreground" data-return-reason>
                                    <option value="">{{ __('storefront.account.return_reason') }}</option>
                                    @foreach ($order['reasons'] as $reason)
                                        <option value="{{ $reason['value'] }}" @selected(old('reason') === $reason['value'])>{{ $reason['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('reason')
                                    <span class="text-xs text-destructive">{{ $message }}</span>
                                @enderror
                                @error('order_id')
                                    <span class="text-xs text-destructive">{{ $message }}</span>
                                @enderror
                            </label>
                            <label class="flex flex-col gap-2">
                                <span class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.account.return_notes') }}</span>
                                <textarea name="notes" rows="4" class="border-b border-input bg-transparent py-2 text-sm outline-none transition-colors focus:border-foreground">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="text-xs text-destructive">{{ $message }}</span>
                                @enderror
                            </label>
                        </div>
                        <div class="border-t border-border px-5 py-3">
                            <x-button type="submit">{{ __('storefront.account.submit_return') }}</x-button>
                        </div>
                    </form>
                </div>
            @elseif (($order['return_request']['href'] ?? null) !== null)
                <a href="{{ $order['return_request']['href'] }}" class="account-card group flex items-center gap-3 rounded-2xl border p-4 transition-colors hover:bg-muted">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-muted">
                        <x-icon name="returns" size="size-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium">{{ __('storefront.account.returns') }}</span>
                        <span class="mt-0.5 block text-[12px] text-muted-foreground">{{ $order['return_status_label'] }}</span>
                    </span>
                    <x-icon name="chevron-right" size="size-4" class="text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                </a>
            @endif
        </div>
    </x-account.shell>
@endsection
