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
        <x-admin.field :label="__('admin.cash.actual')" name="actual" required>
            <x-admin.input name="actual" type="number" value="{{ $register['expected'] }}" required />
        </x-admin.field>
        <div class="flex justify-between text-[13px]">
            <span class="text-muted-foreground">{{ __('admin.cash.difference') }}</span>
            <span class="text-foreground">₺0</span>
        </div>
        <x-admin.button
            type="submit"
            data-confirm
            data-confirm-title="{{ __('admin.confirm.close_register') }}"
            data-confirm-body="{{ __('admin.confirm.close_register_body') }}"
            data-busy-label="{{ __('admin.common.processing') }}"
        >{{ __('admin.cash.submit_close') }}</x-admin.button>
    </form>
@endsection
