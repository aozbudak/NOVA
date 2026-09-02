@extends('layouts.admin')

@section('title', __('admin.inventory.title'))

@section('content')
    <x-admin.page-header :title="__('admin.inventory.title')">
        <x-slot:actions>
            <a href="{{ route('admin.inventory.movements') }}" class="inline-flex h-8 items-center rounded-md border border-border bg-card px-3 text-[12px] text-foreground hover:bg-accent">
                {{ __('admin.inventory.movements') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.inventory.index') }}" class="mb-4 grid gap-2 md:grid-cols-4">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.inventory.search') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <select name="category" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.inventory.filter_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <select name="stock" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.inventory.filter_stock') }}</option>
            <option value="in_stock" @selected(request('stock') === 'in_stock')>{{ __('admin.stock.in_stock') }}</option>
            <option value="low_stock" @selected(request('stock') === 'low_stock')>{{ __('admin.stock.low_stock') }}</option>
            <option value="out_of_stock" @selected(request('stock') === 'out_of_stock')>{{ __('admin.stock.out_of_stock') }}</option>
        </select>
        <input type="date" name="date" value="{{ request('date') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" aria-label="{{ __('admin.inventory.filter_date') }}">
    </form>

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.product') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.variant') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.sku') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.current') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.min') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.inventory.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr @class(['border-b border-border last:border-b-0', 'bg-muted/40' => $row['status'] !== 'in_stock'])>
                        <td class="px-3 py-2.5 text-foreground">{{ $row['product'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['variant'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['sku'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $row['stock'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['min_stock'] }}</td>
                        <td class="px-3 py-2.5"><x-admin.badge :status="$row['status']" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
