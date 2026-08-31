@extends('layouts.storefront')

@section('title', 'Create account')

@section('content')
    <div class="mx-auto max-w-md px-4 py-20">
        <h1 class="text-center font-serif text-4xl">Create account</h1>
        <form method="post" action="{{ route('register.store') }}" class="mt-12 flex flex-col gap-8">
            @csrf
            <x-input name="first_name" label="First name" :required="true" autocomplete="given-name" />
            <x-input name="last_name" label="Last name" :required="true" autocomplete="family-name" />
            <x-input name="email" label="Email" type="email" :required="true" autocomplete="email" />
            <x-input name="password" label="Password" type="password" :required="true" autocomplete="new-password" />
            <x-input name="password_confirmation" label="Confirm password" type="password" :required="true" autocomplete="new-password" />
            <x-button type="submit" class="w-full">Create account</x-button>
        </form>
        <p class="mt-10 text-center text-sm text-muted-foreground">
            Already have an account?
            <a href="{{ route('login') }}" class="ml-1 text-[11px] tracking-label text-foreground uppercase">Login</a>
        </p>
    </div>
@endsection
