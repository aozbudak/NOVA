@extends('layouts.admin')

@section('title', __('admin.dashboard.title'))

@section('content')
    <div class="flex flex-col gap-4">
    <x-admin.card class="overflow-hidden px-5 py-5 md:px-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-medium tracking-nav uppercase text-muted-foreground">{{ $today_label }}</p>
                <h1 class="mt-1 font-serif text-3xl tracking-tight text-foreground">{{ $greeting }}</h1>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-2">
                <div class="flex rounded-xl border border-border bg-muted/50 p-0.5 text-[12px]">
                    @foreach (['today', '7d', '30d', 'custom'] as $key)
                        <a
                            href="{{ route('admin.dashboard', ['range' => $key]) }}"
                            @class([
                                'px-2.5 py-1 rounded-lg',
                                'bg-foreground text-background' => $range === $key,
                                'text-muted-foreground hover:text-foreground' => $range !== $key,
                            ])
                        >{{ __('admin.dashboard.range.'.$key) }}</a>
                    @endforeach
                </div>
                @if ($range === 'custom')
                    <label class="flex flex-col gap-1 text-[11px] text-muted-foreground">
                        {{ __('admin.dashboard.range.from') }}
                        <input type="date" name="from" class="h-9 rounded-xl border border-input bg-background px-2 text-[13px] text-foreground">
                    </label>
                    <label class="flex flex-col gap-1 text-[11px] text-muted-foreground">
                        {{ __('admin.dashboard.range.to') }}
                        <input type="date" name="to" class="h-9 rounded-xl border border-input bg-background px-2 text-[13px] text-foreground">
                    </label>
                    <input type="hidden" name="range" value="custom">
                    <button type="submit" class="h-9 rounded-xl border border-border bg-muted px-3 text-[12px] text-foreground hover:bg-accent">{{ __('admin.dashboard.range.apply') }}</button>
                @endif
            </form>
        </div>
        <div class="mt-5 grid gap-4 border-t border-border pt-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($kpis as $kpi)
                <div class="flex flex-col gap-1">
                    <p class="text-[11px] font-medium tracking-label uppercase text-muted-foreground">{{ $kpi['label'] }}</p>
                    <p class="font-serif text-2xl tracking-tight text-foreground md:text-[1.75rem]">{{ $kpi['value'] }}</p>
                </div>
            @endforeach
        </div>
    </x-admin.card>

    <div data-dashboard-skeleton hidden class="flex flex-col gap-4">
        <x-admin.skeleton.kpi />
        <x-admin.skeleton.chart />
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <section class="admin-card rounded-2xl border p-4 xl:col-span-2">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.charts.sales') }}</h2>
            <x-admin.sparkline class="mt-3" :series="$salesSeries" />
        </section>

        <section class="admin-card rounded-2xl border p-4">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.charts.top_products') }}</h2>
            <ul class="mt-3 flex flex-col gap-3">
                @foreach ($topProducts as $item)
                    <li>
                        <div class="flex items-baseline justify-between gap-2 text-[13px]">
                            <span class="truncate text-foreground">{{ $item['name'] }}</span>
                            <span class="shrink-0 text-muted-foreground">{{ \App\Support\AdminStore::money($item['amount']) }}</span>
                        </div>
                        <p class="text-[11px] text-muted-foreground">{{ $item['qty'] }} {{ __('admin.dashboard.charts.units') }}</p>
                        <div class="mt-1 h-1 bg-muted">
                            <div class="h-1 bg-primary" style="width: {{ $item['share'] }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <div class="grid gap-4 xl:grid-cols-3">
        <section>
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.charts.inventory') }}</h2>
                <a href="{{ route('admin.inventory.index', ['stock' => 'low_stock']) }}" class="text-[12px] text-muted-foreground hover:text-foreground">{{ __('admin.dashboard.view_all') }}</a>
            </div>
            <x-admin.table>
                <x-slot:body>
                    @foreach ($inventoryAlerts as $row)
                        <tr class="border-b border-border last:border-b-0">
                            <x-admin.td :label="__('admin.products.product')">{{ $row['product'] }}</x-admin.td>
                            <x-admin.td :label="__('admin.nav.variants')" tone="muted">{{ $row['variant'] }}</x-admin.td>
                            <x-admin.td :label="__('admin.products.status')" align="end">
                                <x-admin.badge :status="$row['status']" />
                            </x-admin.td>
                        </tr>
                    @endforeach
                </x-slot:body>
            </x-admin.table>
        </section>

        <section class="admin-card rounded-2xl border p-4">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.charts.returns') }}</h2>
            <p class="mt-4 font-serif text-3xl tracking-tight text-foreground">{{ number_format($returnRate, 1) }}%</p>
            <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.dashboard.charts.return_rate') }}</p>
            <div class="mt-4 h-1.5 bg-muted">
                <div class="h-1.5 bg-warning" style="width: {{ min(100, $returnRate * 8) }}%"></div>
            </div>
        </section>

        <section class="admin-card rounded-2xl border p-4">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.charts.cash') }}</h2>
            <dl class="mt-3 flex flex-col gap-2 text-[13px]">
                @foreach ($cash as $row)
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">{{ $row['label'] }}</dt>
                        <dd class="text-foreground">{{ $row['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </div>
    </div>
@endsection
