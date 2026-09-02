@extends('layouts.admin')

@section('title', __('admin.returns.create'))

@section('content')
    <x-admin.page-header :title="__('admin.returns.create')" />

    <form method="GET" action="{{ route('admin.returns.create') }}" class="mb-4 flex max-w-xl gap-2">
        <input type="search" name="sale" value="{{ $query }}" placeholder="{{ __('admin.returns.sale_search') }}" class="h-9 flex-1 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <button type="submit" class="inline-flex h-9 items-center rounded-md border border-border px-3 text-[13px] text-foreground">{{ __('admin.common.search') }}</button>
    </form>

    @if ($query !== '' && $sale === null)
        <p class="text-[13px] text-muted-foreground">{{ __('admin.returns.sale_not_found') }}</p>
    @endif

    @if ($sale)
        <form method="POST" action="{{ route('admin.returns.store') }}" class="flex max-w-2xl flex-col gap-6">
            @csrf
            <input type="hidden" name="sale" value="{{ $sale['number'] }}">

            <section class="rounded-md border border-border bg-card p-4">
                <h2 class="mb-3 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ $sale['number'] }} · {{ $sale['customer'] }}</h2>
                <p class="mb-3 text-[12px] text-muted-foreground">{{ __('admin.returns.select_items') }}</p>
                <ul class="flex flex-col gap-3">
                    @foreach ($sale['items'] as $index => $item)
                        <li>
                            <label class="flex items-start gap-3 text-[13px]">
                                <input type="checkbox" name="items[]" value="{{ $item['sku'] }}" @checked($index === 0) class="mt-1">
                                <span>
                                    <span class="block text-foreground">{{ $item['product'] }}</span>
                                    <span class="block text-[12px] text-muted-foreground">{{ $item['variant'] }}</span>
                                    <span class="block text-[12px] text-muted-foreground">Qty: {{ $item['qty'] }}</span>
                                    <span class="block text-foreground">{{ \App\Support\AdminStore::money($item['total']) }}</span>
                                </span>
                            </label>
                        </li>
                    @endforeach
                </ul>
            </section>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.returns.reason') }}
                    <select name="reason" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                        <option value="wrong_size">{{ __('admin.status.wrong_size') }}</option>
                        <option value="defective">{{ __('admin.status.defective') }}</option>
                        <option value="customer_request">{{ __('admin.status.customer_request') }}</option>
                        <option value="other">{{ __('admin.status.other') }}</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.returns.type') }}
                    <select name="type" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                        <option value="full">{{ __('admin.status.full') }}</option>
                        <option value="partial">{{ __('admin.status.partial') }}</option>
                    </select>
                </label>
            </div>

            <button type="submit" class="inline-flex h-9 w-fit items-center rounded-md bg-primary px-4 text-[13px] font-medium text-primary-foreground">{{ __('admin.common.save') }}</button>
        </form>
    @endif
@endsection
