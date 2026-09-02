@extends('layouts.admin')

@section('title', __('admin.income_expense.add'))

@section('content')
    <x-admin.page-header :title="__('admin.income_expense.add')" />

    <form method="POST" action="{{ route('admin.income-expense.store') }}" class="flex max-w-md flex-col gap-4 rounded-md border border-border bg-card p-4">
        @csrf
        <x-admin.field :label="__('admin.income_expense.type')" name="type" required>
            <x-admin.select name="type">
                <option value="income">{{ __('admin.income_expense.income') }}</option>
                <option value="expense">{{ __('admin.income_expense.expense') }}</option>
            </x-admin.select>
        </x-admin.field>
        <x-admin.field :label="__('admin.income_expense.category')" name="category" required>
            <x-admin.select name="category">
                @foreach ($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </x-admin.select>
        </x-admin.field>
        <x-admin.field :label="__('admin.income_expense.description')" name="description" required>
            <x-admin.input name="description" required />
        </x-admin.field>
        <x-admin.field :label="__('admin.income_expense.amount')" name="amount" required>
            <x-admin.input name="amount" type="number" required />
        </x-admin.field>
        <x-admin.button type="submit">{{ __('admin.common.save') }}</x-admin.button>
    </form>
@endsection
