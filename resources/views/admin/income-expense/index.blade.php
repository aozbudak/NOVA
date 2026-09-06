@extends('layouts.admin')

@section('title', __('admin.income_expense.title'))

@section('content')
    <x-admin.page-header :title="__('admin.income_expense.title')">
        <x-slot:actions>
            <x-admin.button :href="route('admin.income-expense.create')" icon="plus">{{ __('admin.income_expense.add') }}</x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filters :action="route('admin.income-expense.index')" :chips="$chips" :columns="4">
        <x-admin.select name="type">
            <option value="">{{ __('admin.income_expense.filter_type') }}</option>
            <option value="income" @selected($filters['type'] === 'income')>{{ __('admin.income_expense.income') }}</option>
            <option value="expense" @selected($filters['type'] === 'expense')>{{ __('admin.income_expense.expense') }}</option>
        </x-admin.select>
        <x-admin.select name="category">
            <option value="">{{ __('admin.income_expense.filter_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ \App\Support\AdminStore::incomeExpenseCategoryLabel($category) }}</option>
            @endforeach
        </x-admin.select>
        <x-admin.input type="date" name="date" value="{{ $filters['date'] }}" aria-label="{{ __('admin.income_expense.filter_date') }}" />
        <x-admin.select name="user">
            <option value="">{{ __('admin.income_expense.filter_user') }}</option>
            @foreach ($cashiers as $cashier)
                <option value="{{ $cashier }}" @selected($filters['user'] === $cashier)>{{ $cashier }}</option>
            @endforeach
        </x-admin.select>
    </x-admin.filters>

    <x-admin.table :paginator="$transactions">
        <x-slot:head>
            <x-admin.th sort="date">{{ __('admin.income_expense.date') }}</x-admin.th>
            <x-admin.th sort="type">{{ __('admin.income_expense.type') }}</x-admin.th>
            <x-admin.th>{{ __('admin.income_expense.category') }}</x-admin.th>
            <x-admin.th>{{ __('admin.income_expense.description') }}</x-admin.th>
            <x-admin.th sort="amount" align="end">{{ __('admin.income_expense.amount') }}</x-admin.th>
            <x-admin.th>{{ __('admin.income_expense.user') }}</x-admin.th>
            <x-admin.th>{{ __('admin.income_expense.reference') }}</x-admin.th>
        </x-slot:head>
        <x-slot:body>
            @foreach ($transactions as $row)
                <tr class="border-b border-border last:border-b-0">
                    <x-admin.td :label="__('admin.income_expense.date')" tone="muted">{{ $row['date'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.income_expense.type')"><x-admin.badge group="status" :status="$row['type']" /></x-admin.td>
                    <x-admin.td :label="__('admin.income_expense.category')" tone="muted">{{ \App\Support\AdminStore::incomeExpenseCategoryLabel($row['category']) }}</x-admin.td>
                    <x-admin.td :label="__('admin.income_expense.description')">{{ $row['description'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.income_expense.amount')" align="end" :tone="$row['type'] === 'income' ? 'success' : 'danger'">
                        {{ \App\Support\AdminStore::money($row['amount']) }}
                    </x-admin.td>
                    <x-admin.td :label="__('admin.income_expense.user')" tone="muted">{{ $row['user'] }}</x-admin.td>
                    <x-admin.td :label="__('admin.income_expense.reference')" tone="muted">{{ $row['reference'] }}</x-admin.td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-admin.table>
@endsection
