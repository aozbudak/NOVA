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
                <x-admin.field :label="__('admin.users.first_name')" name="first_name" required>
                    <x-admin.input name="first_name" value="{{ old('first_name', $user['first_name'] ?? '') }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.users.last_name')" name="last_name" required>
                    <x-admin.input name="last_name" value="{{ old('last_name', $user['last_name'] ?? '') }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.users.email')" name="email" required>
                    <x-admin.input name="email" type="email" value="{{ old('email', $user['email'] ?? '') }}" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.users.username')" name="username" required>
                    <x-admin.input name="username" value="{{ old('username', $user['username'] ?? '') }}" autocomplete="off" required />
                </x-admin.field>
                <x-admin.field :label="__('admin.users.phone')" name="phone">
                    <x-admin.input name="phone" value="{{ old('phone', $user['phone'] ?? '') }}" />
                </x-admin.field>
                <x-admin.field :label="__('admin.users.role')" name="role" required :help="__('admin.users.role_hint')">
                    <x-admin.input name="role" value="{{ $roleKey }}" list="staff-roles" data-user-role data-role-abilities='@json($roleAbilities)' required />
                    <datalist id="staff-roles">
                        @foreach ($roles as $option)
                            <option value="{{ $option['name'] }}"></option>
                        @endforeach
                    </datalist>
                </x-admin.field>
                <x-admin.field :label="__('admin.users.status')" name="status" required>
                    <x-admin.select name="status">
                        <option value="active" @selected(old('status', $user['status'] ?? 'active') === 'active')>{{ __('admin.status.active') }}</option>
                        <option value="inactive" @selected(old('status', $user['status'] ?? '') === 'inactive')>{{ __('admin.status.inactive') }}</option>
                    </x-admin.select>
                </x-admin.field>
                <x-admin.field class="md:col-span-2" :label="__('admin.users.password')" name="password" :required="! $user" :help="$user ? __('admin.users.password_hint') : __('admin.users.password_help')">
                    <x-admin.input name="password" type="password" autocomplete="new-password" value="" @required(! $user) />
                </x-admin.field>
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
            <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">{{ __('admin.common.save') }}</x-admin.button>
            <x-admin.button variant="ghost" :href="route('admin.users.index')">{{ __('admin.common.cancel') }}</x-admin.button>
        </div>
    </form>
@endsection
