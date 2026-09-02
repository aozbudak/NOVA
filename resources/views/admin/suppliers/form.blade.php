@extends('layouts.admin')

@section('title', __('admin.suppliers.add'))

@section('content')
    <x-admin.page-header :title="__('admin.suppliers.add')" />

    <form method="POST" action="{{ route('admin.suppliers.store') }}" class="flex max-w-3xl flex-col gap-6">
        @csrf
        <section class="admin-card rounded-2xl border p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.suppliers.general') }}</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <x-admin.field class="md:col-span-2" :label="__('admin.suppliers.company')" name="name" required>
                    <x-admin.input name="name" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.suppliers.contact')" name="contact">
                    <x-admin.input name="contact" />
                </x-admin.field>
                <x-admin.field :label="__('admin.suppliers.phone')" name="phone">
                    <x-admin.input name="phone" />
                </x-admin.field>
                <x-admin.field :label="__('admin.suppliers.email')" name="email">
                    <x-admin.input name="email" type="email" />
                </x-admin.field>
                <x-admin.field :label="__('admin.suppliers.status')" name="status">
                    <x-admin.select name="status">
                        <option value="active">{{ __('admin.status.active') }}</option>
                        <option value="inactive">{{ __('admin.status.inactive') }}</option>
                    </x-admin.select>
                </x-admin.field>
                <x-admin.field class="md:col-span-2" :label="__('admin.suppliers.address')" name="address">
                    <x-admin.input name="address" />
                </x-admin.field>
                <x-admin.field class="md:col-span-2" :label="__('admin.suppliers.tax')" name="tax">
                    <x-admin.input name="tax" />
                </x-admin.field>
            </div>
        </section>
        <div class="flex gap-2">
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
            <x-admin.button variant="ghost" :href="route('admin.suppliers.index')">{{ __('admin.common.cancel') }}</x-admin.button>
        </div>
    </form>
@endsection
