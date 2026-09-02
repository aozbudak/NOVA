@extends('layouts.admin')

@section('title', __('admin.suppliers.title'))

@section('content')
    <x-admin.page-header :title="__('admin.suppliers.title')">
        <x-slot:actions>
            <a href="{{ route('admin.suppliers.create') }}" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                <x-icon name="plus" size="size-3.5" />
                {{ __('admin.suppliers.add') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.suppliers.index') }}" class="mb-4 grid gap-2 md:grid-cols-4" data-table-filter>
        <input type="search" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('admin.suppliers.search') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground outline-none placeholder:text-muted-foreground">
        <select name="status" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.suppliers.filter_status') }}</option>
            <option value="active" @selected($filters['status'] === 'active')>{{ __('admin.status.active') }}</option>
            <option value="inactive" @selected($filters['status'] === 'inactive')>{{ __('admin.status.inactive') }}</option>
        </select>
        <input type="date" name="date" value="{{ $filters['date'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" aria-label="{{ __('admin.suppliers.filter_date') }}">
        <select name="sort" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" onchange="this.form.submit()">
            <option value="">{{ __('admin.suppliers.sort') }}</option>
            <option value="name" @selected($filters['sort'] === 'name')>{{ __('admin.suppliers.sort_name') }}</option>
            <option value="purchases" @selected($filters['sort'] === 'purchases')>{{ __('admin.suppliers.sort_purchases') }}</option>
            <option value="recent" @selected($filters['sort'] === 'recent')>{{ __('admin.suppliers.sort_recent') }}</option>
        </select>
    </form>

    @if ($suppliers->isEmpty())
        <div class="rounded-md border border-border bg-card">
            <x-admin.empty :title="__('admin.empty.suppliers.title')">
                {{ __('admin.empty.suppliers.body') }}
                <x-slot:action>
                    <a href="{{ route('admin.suppliers.create') }}" class="inline-flex h-8 items-center rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">{{ __('admin.suppliers.add') }}</a>
                </x-slot:action>
            </x-admin.empty>
        </div>
    @else
    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.suppliers.supplier') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.suppliers.contact') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.suppliers.phone') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.suppliers.email') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.suppliers.purchases') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.suppliers.last_purchase') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.suppliers.status') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($suppliers as $supplier)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-foreground">{{ $supplier['name'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $supplier['contact'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $supplier['phone'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $supplier['email'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($supplier['total']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $supplier['last_purchase'] }}</td>
                        <td class="px-3 py-2.5"><x-admin.badge group="status" :status="$supplier['status']" /></td>
                        <td class="px-3 py-2.5">
                            <a href="{{ route('admin.suppliers.show', $supplier['id']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.view') }}">
                                <x-icon name="eye" size="size-3.5" />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endsection
