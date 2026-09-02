@extends('layouts.admin')

@section('title', __('admin.income_expense.title'))

@section('content')
    <x-admin.page-header :title="__('admin.income_expense.title')">
        <x-slot:actions>
            <a href="{{ route('admin.income-expense.create') }}" class="inline-flex h-8 items-center gap-1.5 rounded-md bg-primary px-3 text-[12px] font-medium text-primary-foreground">
                <x-icon name="plus" size="size-3.5" />
                {{ __('admin.income_expense.add') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="GET" action="{{ route('admin.income-expense.index') }}" class="mb-4 grid gap-2 md:grid-cols-4">
        <select name="type" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.income_expense.filter_type') }}</option>
            <option value="income" @selected($filters['type'] === 'income')>{{ __('admin.income_expense.income') }}</option>
            <option value="expense" @selected($filters['type'] === 'expense')>{{ __('admin.income_expense.expense') }}</option>
        </select>
        <select name="category" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
            <option value="">{{ __('admin.income_expense.filter_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ $filters['date'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" aria-label="{{ __('admin.income_expense.filter_date') }}">
        <select name="user" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" onchange="this.form.submit()">
            <option value="">{{ __('admin.income_expense.filter_user') }}</option>
            @foreach ($cashiers as $cashier)
                <option value="{{ $cashier }}" @selected($filters['user'] === $cashier)>{{ $cashier }}</option>
            @endforeach
        </select>
    </form>

    <div class="overflow-x-auto rounded-md border border-border bg-card">
        <table class="w-full text-left text-[13px]">
            <thead class="border-b border-border text-[11px] tracking-wide text-muted-foreground uppercase">
                <tr>
                    <th class="px-3 py-2 font-medium">{{ __('admin.income_expense.date') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.income_expense.type') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.income_expense.category') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.income_expense.description') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.income_expense.amount') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.income_expense.user') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.income_expense.reference') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('admin.common.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $row)
                    <tr class="border-b border-border last:border-b-0">
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['date'] }}</td>
                        <td class="px-3 py-2.5"><x-admin.badge group="status" :status="$row['type']" /></td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['category'] }}</td>
                        <td class="px-3 py-2.5 text-foreground">{{ $row['description'] }}</td>
                        <td @class(['px-3 py-2.5', 'text-success' => $row['type'] === 'income', 'text-destructive' => $row['type'] === 'expense'])>
                            {{ \App\Support\AdminStore::money($row['amount']) }}
                        </td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['user'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">{{ $row['reference'] }}</td>
                        <td class="px-3 py-2.5 text-muted-foreground">—</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
