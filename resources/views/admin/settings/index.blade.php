@extends('layouts.admin')

@section('title', __('admin.settings.title'))

@section('content')
    <x-admin.page-header :title="__('admin.settings.title')" />

    <div class="grid gap-6 lg:grid-cols-[13rem_1fr]">
        <nav class="flex flex-col gap-0.5" aria-label="{{ __('admin.settings.title') }}">
            @foreach ($categories as $item)
                <a
                    href="{{ route('admin.settings.index', $item['key']) }}"
                    @class([
                        'rounded-md px-3 py-2 text-[13px]',
                        'bg-accent font-medium text-foreground' => $category === $item['key'],
                        'text-muted-foreground hover:bg-accent hover:text-foreground' => $category !== $item['key'],
                    ])
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.settings.update', $category) }}" class="flex max-w-xl flex-col gap-4">
            @csrf
            @method('PUT')
            @include('admin.settings.partials.'.$category, ['settings' => $settings])
            @if ($category !== 'system')
                <div>
                    <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">
                        {{ __('admin.common.save') }}
                    </x-admin.button>
                </div>
            @endif
        </form>
    </div>
@endsection
