@extends('layouts.storefront')

@section('title', __('storefront.account.returns'))

@section('content')
    @php $formOpen = $errors->any(); @endphp

    <x-account.shell>
        <x-account.card>
            <div class="flex items-center justify-between gap-4 px-5 py-4">
                <h1 class="font-serif text-2xl tracking-tight">{{ __('storefront.account.returns') }}</h1>
                <div data-return-trigger @class(['hidden' => $formOpen])>
                    <x-button type="button" data-return-open>{{ __('storefront.account.request_return') }}</x-button>
                </div>
            </div>

            @if (count($returns) === 0)
                <x-account.empty :title="__('storefront.account.no_returns')" icon="returns" class="border-t border-border py-10">
                    {{ __('storefront.account.no_returns_body') }}
                    <x-slot:action>
                        <span data-return-trigger @class(['hidden' => $formOpen])>
                            <x-button type="button" data-return-open>{{ __('storefront.account.request_return') }}</x-button>
                        </span>
                    </x-slot:action>
                </x-account.empty>
            @else
                <div class="hidden grid-cols-[auto_minmax(0,1.1fr)_1fr_1fr_0.9fr_auto] gap-4 border-t border-border px-5 py-2.5 text-[11px] font-medium tracking-label text-muted-foreground uppercase md:grid">
                    <span class="size-10"></span>
                    <span>{{ __('storefront.account.return_number') }}</span>
                    <span>{{ __('storefront.account.order_id') }}</span>
                    <span>{{ __('storefront.account.order_date') }}</span>
                    <span>{{ __('storefront.account.return_reason') }}</span>
                    <span class="text-right">{{ __('storefront.account.order_status') }}</span>
                </div>
                <div class="divide-y divide-border border-t border-border">
                    @foreach ($returns as $return)
                        <a href="{{ $return['href'] }}" class="grid grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-3 px-4 py-3 transition-colors hover:bg-muted md:grid-cols-[auto_minmax(0,1.1fr)_1fr_1fr_0.9fr_auto] md:gap-4 md:px-5">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-muted">
                                <x-icon name="returns" size="size-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ $return['number'] }}</p>
                                <p class="mt-0.5 text-[12px] text-muted-foreground md:hidden">{{ $return['order_number'] }}</p>
                            </div>
                            <p class="hidden text-sm text-muted-foreground md:block">{{ $return['order_number'] }}</p>
                            <p class="hidden text-sm text-muted-foreground md:block">{{ $return['date'] }}</p>
                            <p class="hidden truncate text-sm text-muted-foreground md:block">{{ $return['reason_label'] }}</p>
                            <div class="flex flex-col items-end gap-1">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-[11px]">{{ $return['status_label'] }}</span>
                                <p class="text-[12px] tabular-nums md:hidden">{{ Number::currency($return['amount'], in: $return['currency']) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-account.card>

        <div data-return-panel @class(['hidden' => ! $formOpen])>
            <form method="POST" action="{{ route('account.returns.store') }}" class="account-card overflow-hidden rounded-2xl border" data-return-form>
                @csrf
                <div class="flex items-start justify-between gap-4 border-b border-border px-5 py-4">
                    <div>
                        <h2 class="text-sm font-medium">{{ __('storefront.account.request_return') }}</h2>
                        <p class="mt-1 text-[12px] text-muted-foreground">{{ __('storefront.account.request_return_hint') }}</p>
                    </div>
                    <button type="button" data-return-close class="text-[12px] text-muted-foreground transition-colors hover:text-foreground">
                        {{ __('storefront.account.cancel') }}
                    </button>
                </div>
                <div class="flex flex-col gap-5 p-5">
                    @if (count($returnableOrders) === 0)
                        <p class="text-sm text-muted-foreground">{{ __('storefront.account.no_returnable_orders') }}</p>
                        <div>
                            <x-button href="{{ route('account.orders') }}" variant="outline">{{ __('storefront.account.orders') }}</x-button>
                        </div>
                    @else
                        <label class="flex flex-col gap-2">
                            <span class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.account.select_order') }}</span>
                            <select name="order_id" required class="border-b border-input bg-transparent py-2 text-sm outline-none transition-colors focus:border-foreground">
                                <option value="">{{ __('storefront.account.select_order') }}</option>
                                @foreach ($returnableOrders as $order)
                                    <option value="{{ $order['id'] }}" @selected(old('order_id') === $order['id'])>{{ $order['label'] }}</option>
                                @endforeach
                            </select>
                            @error('order_id')
                                <span class="text-xs text-destructive">{{ $message }}</span>
                            @enderror
                        </label>
                        <label class="flex flex-col gap-2">
                            <span class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.account.return_reason') }}</span>
                            <select name="reason" required class="border-b border-input bg-transparent py-2 text-sm outline-none transition-colors focus:border-foreground">
                                <option value="">{{ __('storefront.account.return_reason') }}</option>
                                @foreach ($reasons as $reason)
                                    <option value="{{ $reason['value'] }}" @selected(old('reason') === $reason['value'])>{{ $reason['label'] }}</option>
                                @endforeach
                            </select>
                            @error('reason')
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
                    @endif
                </div>
                @if (count($returnableOrders) > 0)
                    <div class="border-t border-border px-5 py-3">
                        <x-button type="submit">{{ __('storefront.account.submit_return') }}</x-button>
                    </div>
                @endif
            </form>
        </div>
    </x-account.shell>
@endsection
