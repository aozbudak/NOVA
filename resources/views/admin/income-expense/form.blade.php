@extends('layouts.admin')

@section('title', __('admin.income_expense.add'))

@section('content')
    <x-admin.page-header :title="__('admin.income_expense.add')" />

    <form method="POST" action="{{ route('admin.income-expense.store') }}" class="flex max-w-md flex-col gap-4 rounded-md border border-border bg-card p-4">
        @csrf
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.income_expense.type') }}
            <select name="type" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                <option value="income">{{ __('admin.income_expense.income') }}</option>
                <option value="expense">{{ __('admin.income_expense.expense') }}</option>
            </select>
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.income_expense.category') }}
            <select name="category" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                @foreach ($categories as $category)
                    <option value="{{ $category }}">{{ $category }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.income_expense.description') }}
            <input name="description" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.income_expense.amount') }}
            <input name="amount" type="number" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <button type="submit" class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 text-[13px] font-medium text-primary-foreground">{{ __('admin.common.save') }}</button>
    </form>
@endsection
