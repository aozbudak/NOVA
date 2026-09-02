@extends('layouts.admin')

@section('title', $user ? __('admin.users.edit') : __('admin.users.add'))

@section('content')
    <x-admin.page-header :title="$user ? __('admin.users.edit') : __('admin.users.add')" />

    <form method="POST" action="{{ $user ? route('admin.users.update', $user['id']) : route('admin.users.store') }}" class="flex max-w-4xl flex-col gap-6" autocomplete="off">
        @csrf
        @if ($user)
            @method('PUT')
        @endif

        @php
            $roleKey = old('role', $roleName);
            $selectedAbilities = collect(old('abilities', $user['abilities'] ?? ($roleAbilities[$roleKey] ?? [])))->all();
        @endphp

        <section class="rounded-md border border-border bg-card p-4">
            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.users.first_name') }}
                    <input name="first_name" value="{{ old('first_name', $user['first_name'] ?? '') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" required>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.users.last_name') }}
                    <input name="last_name" value="{{ old('last_name', $user['last_name'] ?? '') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" required>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.users.email') }}
                    <input name="email" type="email" value="{{ old('email', $user['email'] ?? '') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" required>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.users.phone') }}
                    <input name="phone" value="{{ old('phone', $user['phone'] ?? '') }}" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.users.role') }}
                    <input name="role" value="{{ $roleKey }}" list="staff-roles" data-user-role data-role-abilities='@json($roleAbilities)' class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" required>
                    <datalist id="staff-roles">
                        @foreach ($roles as $option)
                            <option value="{{ $option['name'] }}"></option>
                        @endforeach
                    </datalist>
                    <span>{{ __('admin.users.role_hint') }}</span>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground">
                    {{ __('admin.users.status') }}
                    <select name="status" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground">
                        <option value="active" @selected(old('status', $user['status'] ?? 'active') === 'active')>{{ __('admin.status.active') }}</option>
                        <option value="inactive" @selected(old('status', $user['status'] ?? '') === 'inactive')>{{ __('admin.status.inactive') }}</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1.5 text-[12px] text-muted-foreground md:col-span-2">
                    {{ __('admin.users.password') }}
                    <input name="password" type="password" autocomplete="new-password" value="" class="h-9 rounded-md border border-input bg-background px-3 text-[13px] text-foreground" @required(! $user)>
                    <span>{{ $user ? __('admin.users.password_hint') : __('admin.users.password_help') }}</span>
                </label>
            </div>
        </section>

        <section class="rounded-md border border-border bg-card p-4">
            <h2 class="mb-1 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.users.abilities') }}</h2>
            <p class="mb-4 text-[12px] text-muted-foreground">{{ __('admin.users.abilities_hint') }}</p>
            <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3">
                @foreach ($operations as $operation)
                    <label class="flex items-center gap-2 text-[13px] text-foreground">
                        <input type="checkbox" name="abilities[]" value="{{ $operation }}" data-user-ability @checked(in_array($operation, $selectedAbilities, true)) class="size-3.5 rounded-sm border-input">
                        {{ __('admin.nav.'.$operation) }}
                    </label>
                @endforeach
            </div>
        </section>

        <div class="flex items-center gap-2">
            <button type="submit" data-busy-label="{{ __('admin.common.saving') }}" class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-[12px] font-medium text-primary-foreground">{{ __('admin.common.save') }}</button>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex h-9 items-center rounded-md px-4 text-[12px] text-muted-foreground hover:text-foreground">{{ __('admin.common.cancel') }}</a>
        </div>
    </form>
@endsection
