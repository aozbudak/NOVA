@extends('layouts.admin')

@section('title', __('admin.dashboard.title'))

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm text-muted-foreground">{{ $today_label }}</p>
            <h1 class="font-serif text-2xl tracking-tight text-foreground">{{ $greeting }}</h1>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-2">
            <div class="flex rounded-md border border-border bg-card p-0.5 text-[12px]">
                @foreach (['today', '7d', '30d', 'custom'] as $key)
                    <a
                        href="{{ route('admin.dashboard', ['range' => $key]) }}"
                        @class([
                            'px-2.5 py-1 rounded-sm',
                            'bg-primary text-primary-foreground' => $range === $key,
                            'text-muted-foreground hover:text-foreground' => $range !== $key,
                        ])
                    >{{ __('admin.dashboard.range.'.$key) }}</a>
                @endforeach
            </div>
            @if ($range === 'custom')
                <label class="flex flex-col gap-1 text-[11px] text-muted-foreground">
                    {{ __('admin.dashboard.range.from') }}
                    <input type="date" name="from" class="h-8 rounded-md border border-input bg-background px-2 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1 text-[11px] text-muted-foreground">
                    {{ __('admin.dashboard.range.to') }}
                    <input type="date" name="to" class="h-8 rounded-md border border-input bg-background px-2 text-[13px] text-foreground">
                </label>
                <input type="hidden" name="range" value="custom">
                <button type="submit" class="h-8 rounded-md border border-border bg-card px-3 text-[12px] text-foreground hover:bg-accent">{{ __('admin.dashboard.range.apply') }}</button>
            @endif
        </form>
    </div>

    <div data-dashboard-skeleton hidden class="flex flex-col gap-6">
        <x-admin.skeleton.kpi />
        <x-admin.skeleton.chart />
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($kpis as $kpi)
            <article class="rounded-md border border-border bg-card px-4 py-3">
                <p class="text-[11px] tracking-wide text-muted-foreground uppercase">{{ $kpi['label'] }}</p>
                <p class="mt-1 font-serif text-2xl tracking-tight text-foreground">{{ $kpi['value'] }}</p>
            </article>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-3">
        <section class="rounded-md border border-border bg-card p-4 xl:col-span-2">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.charts.sales') }}</h2>
            <x-admin.sparkline class="mt-3" :series="$salesSeries" />
        </section>

        <section class="rounded-md border border-border bg-card p-4">
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

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <section class="rounded-md border border-border bg-card">
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
                <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.charts.inventory') }}</h2>
                <a href="{{ route('admin.inventory.index', ['stock' => 'low_stock']) }}" class="text-[12px] text-muted-foreground hover:text-foreground">{{ __('admin.dashboard.view_all') }}</a>
            </div>
            <table class="w-full text-left text-[13px]">
                <tbody>
                    @foreach ($inventoryAlerts as $row)
                        <tr class="border-b border-border last:border-b-0">
                            <td class="px-4 py-2.5 text-foreground">{{ $row['product'] }}</td>
                            <td class="px-4 py-2.5 text-muted-foreground">{{ $row['variant'] }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <x-admin.badge :status="$row['status']" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="text-sm font-medium text-foreground">{{ __('admin.dashboard.charts.returns') }}</h2>
            <p class="mt-4 font-serif text-3xl tracking-tight text-foreground">{{ number_format($returnRate, 1) }}%</p>
            <p class="mt-1 text-[12px] text-muted-foreground">{{ __('admin.dashboard.charts.return_rate') }}</p>
            <div class="mt-4 h-1.5 bg-muted">
                <div class="h-1.5 bg-warning" style="width: {{ min(100, $returnRate * 8) }}%"></div>
            </div>
        </section>

        <section class="rounded-md border border-border bg-card p-4">
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
@endsection
