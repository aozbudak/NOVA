@extends('layouts.admin')

@section('title', __('admin.dashboard.title'))

@section('content')
    <x-admin.page-header :title="__('admin.dashboard.title')" :description="__('admin.dashboard.subtitle')" />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($metrics as $metric)
            <article class="rounded-md border border-border bg-card px-4 py-3">
                <p class="text-[12px] text-muted-foreground">{{ $metric['label'] }}</p>
                <p class="mt-1 font-serif text-2xl tracking-tight text-foreground">{{ $metric['value'] }}</p>
                <p class="mt-1 text-[12px] text-muted-foreground">{{ $metric['hint'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-5">
        <section class="rounded-md border border-border bg-card xl:col-span-3">
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
                <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.recent_sales') }}</h2>
                <a href="{{ route('admin.sales.index') }}" class="text-[12px] text-muted-foreground hover:text-foreground">{{ __('admin.dashboard.view_all') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.reference') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.customer') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.channel') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.total') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.time') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                            <tr class="border-b border-border last:border-b-0">
                                <td class="px-4 py-2.5 text-foreground">{{ $sale['ref'] }}</td>
                                <td class="px-4 py-2.5 text-foreground">{{ $sale['customer'] }}</td>
                                <td class="px-4 py-2.5 text-muted-foreground">{{ $sale['channel'] }}</td>
                                <td class="px-4 py-2.5 text-foreground">{{ $sale['total'] }}</td>
                                <td class="px-4 py-2.5 text-muted-foreground">{{ $sale['time'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-md border border-border bg-card xl:col-span-2">
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
                <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.low_stock') }}</h2>
                <a href="{{ route('admin.inventory.index') }}" class="text-[12px] text-muted-foreground hover:text-foreground">{{ __('admin.dashboard.view_all') }}</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[13px]">
                    <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                        <tr>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.sku') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.product') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.qty') }}</th>
                            <th class="px-4 py-2 font-medium">{{ __('admin.dashboard.location') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stock as $row)
                            <tr class="border-b border-border last:border-b-0">
                                <td class="px-4 py-2.5 text-muted-foreground">{{ $row['sku'] }}</td>
                                <td class="px-4 py-2.5 text-foreground">{{ $row['name'] }}</td>
                                <td class="px-4 py-2.5 text-destructive">{{ $row['qty'] }}</td>
                                <td class="px-4 py-2.5 text-muted-foreground">{{ $row['location'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
