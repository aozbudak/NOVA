@extends('layouts.admin')

@section('title', __('admin.cash.close'))

@section('content')
    <x-admin.page-header :title="__('admin.cash.close')" />

    <form method="POST" action="{{ route('admin.cash.close.store') }}" class="flex max-w-md flex-col gap-4 rounded-md border border-border bg-card p-4">
        @csrf
        <dl class="flex flex-col gap-2 text-[13px]">
            <div class="flex justify-between gap-3">
                <dt class="text-muted-foreground">{{ __('admin.cash.expected') }}</dt>
                <dd class="text-foreground">{{ \App\Support\AdminStore::money($register['expected']) }}</dd>
            </div>
        </dl>
        <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
            {{ __('admin.cash.actual') }}
            <input name="actual" type="number" value="{{ $register['expected'] }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
        </label>
        <div class="flex justify-between text-[13px]">
            <span class="text-muted-foreground">{{ __('admin.cash.difference') }}</span>
            <span class="text-foreground">₺0</span>
        </div>
        <button type="submit" class="inline-flex h-9 items-center justify-center rounded-md bg-primary px-4 text-[13px] font-medium text-primary-foreground">{{ __('admin.cash.submit_close') }}</button>
    </form>
@endsection
