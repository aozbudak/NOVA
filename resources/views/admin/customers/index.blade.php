@extends('layouts.admin')

@section('title', __('admin.customers.title'))

@section('content')
    <x-admin.page-header :title="__('admin.customers.title')">
        <x-slot:actions>
            <button type="button" data-open-layer="add-customer" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                <x-icon name="plus" size="size-3.5" />
                {{ __('admin.customers.add') }}
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($customers->isEmpty())
        <div class="rounded-md border border-border bg-card">
            <x-admin.empty :title="__('admin.empty.customers.title')">
                {{ __('admin.empty.customers.body') }}
                <x-slot:action>
                    <button type="button" data-open-layer="add-customer" class="inline-flex h-8 items-center rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">{{ __('admin.customers.add') }}</button>
                </x-slot:action>
            </x-admin.empty>
        </div>
    @else
    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.name') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.phone') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.email') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.orders') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.spent') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.customers.last_purchase') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-foreground">{{ $customer['name'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $customer['phone'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $customer['email'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $customer['orders'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ \App\Support\AdminStore::money($customer['spent']) }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $customer['last_purchase'] }}</td>
                        <td class="px-3 py-2.5">
                            <a href="{{ route('admin.customers.show', $customer['id']) }}" class="inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:bg-accent hover:text-foreground" aria-label="{{ __('admin.common.view') }}">
                                <x-icon name="eye" size="size-3.5" />
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <x-admin.drawer name="add-customer" :title="__('admin.customers.add')">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="flex flex-col gap-4">
            @csrf
            <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                {{ __('admin.customers.name') }}
                <input name="name" required class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            </label>
            <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                {{ __('admin.customers.email') }}
                <input name="email" type="email" required class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            </label>
            <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                {{ __('admin.customers.phone') }}
                <input name="phone" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            </label>
            <button type="submit" data-busy-label="{{ __('admin.common.saving') }}" class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 text-[12px] font-medium text-primary-foreground">
                {{ __('admin.common.save') }}
            </button>
        </form>
    </x-admin.drawer>
@endsection
