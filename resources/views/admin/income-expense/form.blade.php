@extends('layouts.admin')

@section('title', __('admin.income_expense.add'))

@section('content')
    <x-admin.page-header :title="__('admin.income_expense.add')" />

    <form method="POST" action="{{ route('admin.income-expense.store') }}" class="flex max-w-md flex-col gap-4 admin-card rounded-2xl border p-4">
        @csrf
        <x-admin.field :label="__('admin.income_expense.type')" name="type" required>
            <x-admin.select name="type">
                <option value="income" @selected(old('type') === 'income')>{{ __('admin.income_expense.income') }}</option>
                <option value="expense" @selected(old('type', 'expense') === 'expense')>{{ __('admin.income_expense.expense') }}</option>
            </x-admin.select>
        </x-admin.field>
        <x-admin.field :label="__('admin.income_expense.category')" name="category" required>
            <x-admin.select name="category">
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(old('category') === $category)>{{ \App\Support\AdminStore::incomeExpenseCategoryLabel($category) }}</option>
                @endforeach
            </x-admin.select>
        </x-admin.field>
        <x-admin.field :label="__('admin.income_expense.custom_category')" name="custom_category" :help="__('admin.income_expense.custom_category_help')">
            <x-admin.input name="custom_category" value="{{ old('custom_category') }}" autocomplete="off" />
        </x-admin.field>
        <x-admin.field :label="__('admin.income_expense.description')" name="description" required>
            <x-admin.input name="description" value="{{ old('description') }}" required />
        </x-admin.field>
        <x-admin.field :label="__('admin.income_expense.amount')" name="amount" required>
            <x-admin.input name="amount" type="number" value="{{ old('amount') }}" required />
        </x-admin.field>
        <x-admin.button type="submit">{{ __('admin.common.save') }}</x-admin.button>
    </form>
@endsection
