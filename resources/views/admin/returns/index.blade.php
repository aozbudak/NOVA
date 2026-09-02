@extends('layouts.admin')

@section('title', __('admin.returns.title'))

@section('content')
    <x-admin.page-header :title="__('admin.returns.title')">
        <x-slot:actions>
            <a href="{{ route('admin.returns.create') }}" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                <x-icon name="plus" size="size-3.5" />
                {{ __('admin.returns.create') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.returns.index') }}" class="mb-4 grid gap-2 md:grid-cols-6">
        <input type="search" name="return" value="{{ $filters['return'] }}" placeholder="{{ __('admin.returns.filter_return') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <input type="search" name="sale" value="{{ $filters['sale'] }}" placeholder="{{ __('admin.returns.filter_sale') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <input type="search" name="customer" value="{{ $filters['customer'] }}" placeholder="{{ __('admin.returns.filter_customer') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <input type="date" name="date" value="{{ $filters['date'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" aria-label="{{ __('admin.returns.date') }}">
        <select name="reason" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.returns.reason') }}</option>
            <option value="wrong_size" @selected($filters['reason'] === 'wrong_size')>{{ __('admin.status.wrong_size') }}</option>
            <option value="defective" @selected($filters['reason'] === 'defective')>{{ __('admin.status.defective') }}</option>
            <option value="customer_request" @selected($filters['reason'] === 'customer_request')>{{ __('admin.status.customer_request') }}</option>
            <option value="other" @selected($filters['reason'] === 'other')>{{ __('admin.status.other') }}</option>
        </select>
        <select name="status" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" onchange="this.form.submit()">
            <option value="">{{ __('admin.returns.status') }}</option>
            <option value="completed" @selected($filters['status'] === 'completed')>{{ __('admin.status.completed') }}</option>
            <option value="open" @selected($filters['status'] === 'open')>{{ __('admin.status.open') }}</option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.returns.number') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.returns.sale') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.returns.customer') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.returns.products') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.returns.amount') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.returns.reason') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.returns.date') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.returns.status') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($returns as $row)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-foreground">{{ $row['number'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['sale'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $row['customer'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['products'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($row['amount']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ __('admin.status.'.$row['reason']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['date'] }}</td>
                        <td class="px-3 py-2.5"><x-admin.badge group="status" :status="$row['status']" /></td>
                        <td class="px-3 py-2.5">
                            <a href="{{ route('admin.returns.show', $row['id']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.view') }}">
                                <x-icon name="eye" size="size-3.5" />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
