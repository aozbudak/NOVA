@extends('layouts.storefront')

@section('title', 'Welcome back')

@section('content')
    <div class="mx-auto max-w-md px-4 py-20">
        <h1 class="text-center font-serif text-4xl">Welcome back</h1>
        <form method="post" action="{{ route('login.store') }}" class="mt-12 flex flex-col gap-8">
            @csrf
            <x-input name="email" label="Email" type="email" :required="true" autocomplete="email" />
            <x-input name="password" label="Password" type="password" :required="true" autocomplete="current-password" />
            <a href="{{ route('pages.show', 'contact') }}" class="text-[11px] tracking-label uppercase text-muted-foreground">Forgot password?</a>
            <x-button type="submit" class="w-full">Login</x-button>
        </form>
        <p class="mt-10 text-center text-sm text-muted-foreground">
            Don't have an account?
            <a href="{{ route('register') }}" class="ml-1 text-foreground tracking-label uppercase text-[11px]">Create account</a>
        </p>
    </div>
@endsection
