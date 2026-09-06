@extends('layouts.admin')

@section('title', __('admin.nav.pos'))

@section('content')
    <div
        data-pos
        data-pos-insufficient="{{ __('admin.pos.insufficient_stock') }}"
        data-pos-discount-error="{{ __('admin.pos.discount_exceeds') }}"
        data-pos-sale-no-label="{{ __('admin.sales.number') }}"
        data-pos-total-label="{{ __('admin.pos.total') }}"
        data-pos-payment-label="{{ __('admin.pos.payment') }}"
        data-pos-cash-label="{{ __('admin.pos.cash') }}"
        data-pos-card-label="{{ __('admin.pos.card') }}"
        data-pos-other-label="{{ __('admin.pos.other') }}"
        data-pos-note-required="{{ __('admin.pos.note_required') }}"
        data-pos-copied="{{ __('admin.pos.copied') }}"
        class="grid h-full min-h-0 lg:grid-cols-[minmax(0,1fr)_24rem]"
    >
        <section class="flex min-h-0 flex-col border-b border-border lg:border-r lg:border-b-0">
            <div class="border-b border-border px-4 py-3">
                <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.pos.search') }}</p>
                <label class="mt-2 flex items-center gap-2 rounded-xl border border-input bg-background px-3">
                    <x-icon name="barcode" size="size-4" class="text-muted-foreground" />
                    <input
                        type="text"
                        data-pos-search
                        autofocus
                        autocomplete="off"
                        placeholder="{{ __('admin.pos.barcode') }}"
                        class="h-11 w-full bg-transparent text-[15px] text-foreground outline-none placeholder:text-muted-foreground"
                    >
                </label>
                <p class="mt-1.5 text-[11px] text-muted-foreground">{{ __('admin.pos.barcode_hint') }} · {{ __('admin.pos.keys') }}</p>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto">
                <p class="px-4 py-2 text-[11px] tracking-wide text-muted-foreground uppercase">{{ __('admin.pos.results') }}</p>
                <ul data-pos-results data-stock-label="{{ __('admin.pos.stock') }}" class="flex flex-col">
                    @foreach ($items as $item)
                        <li>
                            <button
                                type="button"
                                data-pos-item
                                data-sku="{{ $item['sku'] }}"
                                data-barcode="{{ $item['barcode'] }}"
                                data-name="{{ $item['name'] }}"
                                data-brand="{{ $item['brand'] ?? '' }}"
                                data-variant="{{ $item['variant'] }}"
                                data-price="{{ $item['price'] }}"
                                data-stock="{{ $item['stock'] }}"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-left hover:bg-accent"
                            >
                                <img src="{{ $item['image'] }}" alt="" width="36" height="44" class="h-11 w-9 object-cover">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-[13px] text-foreground">{{ $item['name'] }}</span>
                                    <span class="block truncate text-[12px] text-muted-foreground">{{ $item['brand'] ?? '' }}{{ filled($item['brand'] ?? null) ? ' · ' : '' }}{{ $item['variant'] }} · {{ $item['sku'] }} · {{ $item['barcode'] }}</span>
                                </span>
                                <span class="shrink-0 text-right">
                                    <span class="block text-[13px] text-foreground">{{ \App\Support\AdminStore::money($item['price']) }}</span>
                                    <span class="block text-[11px] text-muted-foreground">{{ __('admin.pos.stock') }} {{ $item['stock'] }}</span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
                <p data-pos-empty hidden class="px-4 py-8 text-center text-[13px] text-muted-foreground">{{ __('admin.pos.empty') }}</p>
            </div>
        </section>

        <aside class="flex min-h-0 flex-col bg-card">
            <div class="flex items-center justify-between gap-2 border-b border-border px-4 py-3">
                <p class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.pos.cart') }}</p>
                <button type="button" data-pos-clear class="text-[11px] text-muted-foreground hover:text-foreground">{{ __('admin.pos.clear') }}</button>
            </div>
            <p data-pos-last-sale hidden class="flex items-center justify-between gap-2 border-b border-border px-4 py-2 text-[12px]">
                <span class="min-w-0 truncate text-foreground">
                    <span class="text-muted-foreground">{{ __('admin.pos.last_sale') }}</span>
                    <span data-pos-last-sale-number class="ml-1 font-medium tracking-wide"></span>
                </span>
                <button type="button" data-pos-copy-sale class="shrink-0 text-[11px] text-muted-foreground hover:text-foreground">{{ __('admin.pos.copy_number') }}</button>
            </p>
            <div data-pos-cart class="min-h-0 flex-1 overflow-x-auto overflow-y-auto">
                <p data-pos-cart-empty class="px-4 py-8 text-center text-[13px] text-muted-foreground">{{ __('admin.pos.cart_empty') }}</p>
                <table
                    class="hidden w-full min-w-[28rem] text-left text-[12px]"
                    data-pos-cart-table
                    data-admin-table
                    data-label-product="{{ __('admin.pos.product') }}"
                    data-label-sku="{{ __('admin.sales.sku') }}"
                    data-label-qty="{{ __('admin.pos.qty') }}"
                    data-label-unit="{{ __('admin.pos.unit') }}"
                    data-label-discount="{{ __('admin.pos.discount') }}"
                    data-label-total="{{ __('admin.pos.line_total') }}"
                    data-label-remove="{{ __('admin.pos.remove') }}"
                >
                    <thead class="sticky top-0 bg-card text-[10px] tracking-wide text-muted-foreground uppercase">
                        <tr>
                            <th class="px-3 py-2 font-medium">{{ __('admin.pos.product') }}</th>
                            <th class="px-3 py-2 font-medium">{{ __('admin.pos.qty') }}</th>
                            <th class="px-3 py-2 font-medium">{{ __('admin.pos.unit') }}</th>
                            <th class="px-3 py-2 font-medium">{{ __('admin.pos.discount') }}</th>
                            <th class="px-3 py-2 font-medium">{{ __('admin.pos.line_total') }}</th>
                        </tr>
                    </thead>
                    <tbody data-pos-cart-body></tbody>
                </table>
            </div>
            <div class="border-t border-border px-4 py-4">
                <dl class="flex flex-col gap-1.5 text-[13px]">
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">{{ __('admin.pos.subtotal') }}</dt>
                        <dd data-pos-subtotal class="text-foreground">₺0</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-muted-foreground">{{ __('admin.pos.discount') }}</dt>
                        <dd data-pos-discount class="text-foreground">₺0</dd>
                    </div>
                    <div class="flex justify-between text-[15px] font-medium">
                        <dt>{{ __('admin.pos.total') }}</dt>
                        <dd data-pos-total>₺0</dd>
                    </div>
                </dl>
                <div class="mt-4 grid grid-cols-3 gap-2">
                    <button type="button" data-pos-pay="cash" class="h-10 rounded-md bg-primary text-[12px] font-medium text-primary-foreground">{{ __('admin.pos.cash') }}</button>
                    <button type="button" data-pos-pay="card" class="h-10 rounded-md border border-border bg-background text-[12px] font-medium text-foreground">{{ __('admin.pos.card') }}</button>
                    <button type="button" data-pos-pay="other" class="h-10 rounded-md border border-border bg-background text-[12px] font-medium text-foreground">{{ __('admin.pos.other') }}</button>
                </div>
                <div data-pos-other-note hidden class="mt-3 rounded-xl border border-border p-3">
                    <label for="pos-other-note" class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.pos.note') }}</label>
                    <textarea
                        id="pos-other-note"
                        data-pos-note
                        rows="2"
                        maxlength="255"
                        placeholder="{{ __('admin.pos.note_placeholder') }}"
                        class="mt-1.5 w-full rounded-xl border border-input bg-background px-3 py-2 text-[13px] text-foreground outline-none placeholder:text-muted-foreground"
                    ></textarea>
                    <div class="mt-2 flex justify-end gap-2">
                        <button type="button" data-pos-note-cancel class="h-9 rounded-xl px-3 text-[12px] text-muted-foreground hover:bg-muted hover:text-foreground">{{ __('admin.common.cancel') }}</button>
                        <button type="button" data-pos-note-confirm class="h-9 rounded-xl bg-primary px-3 text-[12px] font-medium text-primary-foreground">{{ __('admin.pos.pay') }}</button>
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
