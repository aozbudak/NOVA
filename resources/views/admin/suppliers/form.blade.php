@extends('layouts.admin')

@section('title', __('admin.suppliers.add'))

@section('content')
    <x-admin.page-header :title="__('admin.suppliers.add')" />

    <form method="POST" action="{{ route('admin.suppliers.store') }}" class="flex max-w-3xl flex-col gap-6">
        @csrf
        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.suppliers.general') }}</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground md:col-span-2">
                    {{ __('admin.suppliers.company') }}
                    <input name="name" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.suppliers.contact') }}
                    <input name="contact" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.suppliers.phone') }}
                    <input name="phone" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.suppliers.email') }}
                    <input name="email" type="email" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.suppliers.status') }}
                    <select name="status" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                        <option value="active">{{ __('admin.status.active') }}</option>
                        <option value="inactive">{{ __('admin.status.inactive') }}</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground md:col-span-2">
                    {{ __('admin.suppliers.address') }}
                    <input name="address" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground md:col-span-2">
                    {{ __('admin.suppliers.tax') }}
                    <input name="tax" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
            </div>
        </section>
        <div class="flex gap-2">
            <button type="submit" class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-[13px] font-medium text-primary-foreground">{{ __('admin.common.save') }}</button>
            <a href="{{ route('admin.suppliers.index') }}" class="inline-flex h-9 items-center rounded-md border border-border px-4 text-[13px] text-foreground">{{ __('admin.common.cancel') }}</a>
        </div>
    </form>
@endsection
