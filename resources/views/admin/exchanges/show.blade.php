@extends('layouts.admin')

@section('title', $exchange['number'])

@section('content')
    <x-admin.page-header :title="$exchange['number']" />

    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.exchanges.original') }}</h2>
            <p class="mt-3 text-[15px] text-foreground">{{ $exchange['original']['product'] }} / {{ $exchange['original']['variant'] }}</p>
            <p class="mt-1 text-[13px] text-muted-foreground">{{ $exchange['original']['sku'] }} · {{ \App\Support\AdminStore::money($exchange['original']['price']) }}</p>
            <p class="mt-3 text-[12px] text-muted-foreground">{{ __('admin.exchanges.stock_in') }}</p>
        </section>
        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.exchanges.new') }}</h2>
            <p class="mt-3 text-[15px] text-foreground">{{ $exchange['new']['product'] }} / {{ $exchange['new']['variant'] }}</p>
            <p class="mt-1 text-[13px] text-muted-foreground">{{ $exchange['new']['sku'] }} · {{ \App\Support\AdminStore::money($exchange['new']['price']) }}</p>
            <p class="mt-3 text-[12px] text-muted-foreground">{{ __('admin.exchanges.stock_out') }}</p>
        </section>
    </div>

    <section class="mt-4 rounded-md border border-border bg-card p-4">
        <h2 class="text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.exchanges.difference') }}</h2>
        <div class="mt-3 flex items-center gap-3">
            <x-admin.badge group="status" :status="$exchange['difference']" />
            @if ($exchange['difference_amount'] !== 0)
                <span class="text-[13px] text-foreground">{{ \App\Support\AdminStore::money(abs($exchange['difference_amount'])) }}</span>
            @endif
        </div>
    </section>
@endsection
