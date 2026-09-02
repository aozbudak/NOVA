<form method="GET" action="{{ route('admin.reports.show', $report['key']) }}" class="mb-6 flex flex-wrap items-end gap-2">
    <div class="flex rounded-xl border border-border bg-muted/50 p-0.5 text-[12px]">
        @foreach (['today', 'week', 'month', 'custom'] as $key)
            <a
                href="{{ route('admin.reports.show', ['report' => $report['key'], 'range' => $key]) }}"
                @class([
                    'px-2.5 py-1 rounded-lg',
                    'bg-foreground text-background' => ($report['range'] ?? '') === $key,
                    'text-muted-foreground hover:text-foreground' => ($report['range'] ?? '') !== $key,
                ])
            >{{ __('admin.reports.range.'.$key) }}</a>
        @endforeach
    </div>
    @if (($report['range'] ?? '') === 'custom')
        <label class="flex flex-col gap-1 text-[11px] text-muted-foreground">
            {{ __('admin.dashboard.range.from') }}
            <input type="date" name="from" value="{{ $report['from'] }}" class="h-8 rounded-md border border-input bg-background px-2 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1 text-[11px] text-muted-foreground">
            {{ __('admin.dashboard.range.to') }}
            <input type="date" name="to" value="{{ $report['to'] }}" class="h-8 rounded-md border border-input bg-background px-2 text-[13px] text-foreground">
        </label>
        <input type="hidden" name="range" value="custom">
        <button type="submit" class="h-8 admin-card rounded-2xl border px-3 text-[12px] text-foreground hover:bg-accent">{{ __('admin.dashboard.range.apply') }}</button>
    @endif
</form>

@include('admin.reports.partials.metrics', ['report' => $report])

<div class="mt-6 grid gap-4 xl:grid-cols-3">
    <section class="admin-card rounded-2xl border p-4 xl:col-span-2">
        <h2 class="text-sm font-medium text-foreground">{{ __('admin.reports.charts.trend') }}</h2>
        <x-admin.sparkline class="mt-3" :series="$report['trend']" />
    </section>
    <section class="admin-card rounded-2xl border p-4">
        <h2 class="text-sm font-medium text-foreground">{{ __('admin.reports.charts.payment') }}</h2>
        <ul class="mt-3 flex flex-col gap-3">
            @foreach ($report['payments'] as $payment)
                <li>
                    <div class="flex items-baseline justify-between gap-2 text-[13px]">
                        <span class="text-foreground">{{ $payment['label'] }}</span>
                        <span class="text-muted-foreground">{{ \App\Support\AdminStore::money($payment['amount']) }}</span>
                    </div>
                    <div class="mt-1 h-1 bg-muted">
                        <div class="h-1 bg-primary" style="width: {{ $payment['share'] }}%"></div>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</div>

<section class="mt-4 admin-card rounded-2xl border p-4">
    <h2 class="text-sm font-medium text-foreground">{{ __('admin.reports.charts.top_products') }}</h2>
    <ul class="mt-3 flex flex-col gap-3">
        @foreach ($report['top_products'] as $item)
            <li>
                <div class="flex items-baseline justify-between gap-2 text-[13px]">
                    <span class="truncate text-foreground">{{ $item['product'] }}</span>
                    <span class="shrink-0 text-muted-foreground">{{ \App\Support\AdminStore::money($item['net']) }}</span>
                </div>
                <p class="text-[11px] text-muted-foreground">{{ $item['quantity'] }} {{ __('admin.dashboard.charts.units') }}</p>
                <div class="mt-1 h-1 bg-muted">
                    <div class="h-1 bg-primary" style="width: {{ $item['share'] }}%"></div>
                </div>
            </li>
        @endforeach
    </ul>
</section>

<div class="mt-4">
    @include('admin.reports.partials.table', ['report' => $report])
</div>
