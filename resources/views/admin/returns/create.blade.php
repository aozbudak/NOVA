@extends('layouts.admin')

@section('title', __('admin.returns.create'))

@section('content')
    <x-admin.page-header :title="__('admin.returns.create')" />

    <form method="GET" action="{{ route('admin.returns.create') }}" class="mb-4 flex max-w-xl gap-2">
        <x-admin.input type="search" name="sale" value="{{ $query }}" placeholder="{{ __('admin.returns.sale_search') }}" />
        <x-admin.button type="submit" variant="secondary">{{ __('admin.common.search') }}</x-admin.button>
    </form>

    @if ($query !== '' && $sale === null)
        <p class="text-[13px] text-muted-foreground">{{ __('admin.returns.sale_not_found') }}</p>
    @endif

    @if ($sale)
        @if ($sale['status'] === 'returned')
            <p class="mb-4 text-[13px] text-destructive">{{ __('admin.returns.already_returned') }}</p>
        @endif

        <section class="admin-card mb-4 rounded-2xl border p-4">
            <h2 class="mb-3 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ $sale['number'] }}</h2>
            <dl class="grid gap-3 text-[13px] md:grid-cols-4">
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.sales.date') }}</dt>
                    <dd class="text-foreground">{{ $sale['date'] }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.sales.customer') }}</dt>
                    <dd class="text-foreground">{{ $sale['customer'] }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.sales.payment') }}</dt>
                    <dd class="text-foreground">{{ __('admin.pos.'.$sale['payment']) }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">{{ __('admin.sales.status') }}</dt>
                    <dd><x-admin.badge group="status" :status="$sale['status']" /></dd>
                </div>
            </dl>
        </section>

        <x-admin.table class="mb-4">
            <x-slot:head>
                <x-admin.th>{{ __('admin.sales.product') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.variant') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.quantity') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.unit') }}</x-admin.th>
                <x-admin.th>{{ __('admin.sales.total') }}</x-admin.th>
            </x-slot:head>
            <x-slot:body>
                @foreach ($sale['items'] as $item)
                    <tr class="border-b border-border last:border-b-0">
                        <x-admin.td :label="__('admin.sales.product')">{{ $item['product'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.variant')" tone="muted">{{ $item['variant'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.quantity')">{{ $item['qty'] }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.unit')">{{ \App\Support\AdminStore::money($item['unit']) }}</x-admin.td>
                        <x-admin.td :label="__('admin.sales.total')">{{ \App\Support\AdminStore::money($item['total']) }}</x-admin.td>
                    </tr>
                @endforeach
            </x-slot:body>
        </x-admin.table>

        @if ($sale['status'] !== 'returned')
            <form method="POST" action="{{ route('admin.returns.store') }}" class="flex max-w-2xl flex-col gap-6">
                @csrf
                <input type="hidden" name="sale" value="{{ $sale['number'] }}">
                <p class="text-[12px] text-muted-foreground">{{ __('admin.returns.all_items') }}</p>
                <x-admin.field :label="__('admin.returns.reason')" name="reason">
                    <x-admin.select name="reason">
                        <option value="customer_changed_mind">{{ __('admin.status.customer_changed_mind') }}</option>
                        <option value="wrong_size">{{ __('admin.status.wrong_size') }}</option>
                        <option value="defective">{{ __('admin.status.defective') }}</option>
                        <option value="wrong_product">{{ __('admin.status.wrong_product') }}</option>
                        <option value="other">{{ __('admin.status.other') }}</option>
                    </x-admin.select>
                </x-admin.field>
                <x-admin.button type="submit">{{ __('admin.common.save') }}</x-admin.button>
            </form>
        @endif
    @endif
@endsection
