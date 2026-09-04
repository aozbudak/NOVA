@extends('layouts.admin')

@section('title', __('admin.site.title'))

@section('content')
    <x-admin.page-header :title="__('admin.site.title')" :description="__('admin.site.hint')" />

    <div class="grid items-start gap-4 lg:grid-cols-[15rem_minmax(0,1fr)]">
        <nav class="admin-card flex flex-col gap-1 rounded-2xl border p-2" aria-label="{{ __('admin.site.title') }}">
            @foreach ($sections as $item)
                <a
                    href="{{ route('admin.site.index', $item['key']) }}"
                    @class([
                        'rounded-xl px-3 py-2 text-[12px] transition-colors',
                        'bg-muted font-medium text-foreground' => $section === $item['key'],
                        'text-muted-foreground hover:bg-muted hover:text-foreground' => $section !== $item['key'],
                    ])
                >{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <form method="POST" action="{{ route('admin.site.update', $section) }}" class="flex max-w-3xl flex-col gap-4">
            @csrf
            @method('PUT')

            <section class="admin-card rounded-2xl border p-4">
                <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ __('admin.site.sections.'.$section) }}</h2>
                <div class="grid gap-4">
                    <x-admin.field :label="__('admin.site.heading')" name="heading" required>
                        <x-admin.input name="heading" value="{{ old('heading', $content['heading']) }}" />
                    </x-admin.field>
                    @if ($section === 'journal')
                        <x-admin.field :label="__('admin.site.journal_text')" name="text" required>
                            <x-admin.textarea name="text" rows="3">{{ old('text', $content['text']) }}</x-admin.textarea>
                        </x-admin.field>
                    @endif
                </div>
            </section>

            @foreach ($content['pages'] as $slug => $page)
                <section class="admin-card rounded-2xl border p-4">
                    <h2 class="mb-4 text-[11px] font-medium tracking-wide text-muted-foreground uppercase">{{ old('pages.'.$slug.'.title', $page['title']) }}</h2>
                    <div class="grid gap-4">
                        <x-admin.field :label="__('admin.site.page_title')" :name="'pages.'.$slug.'.title'" required>
                            <x-admin.input name="pages[{{ $slug }}][title]" :id="'pages.'.$slug.'.title'" value="{{ old('pages.'.$slug.'.title', $page['title']) }}" />
                        </x-admin.field>
                        <x-admin.field :label="__('admin.site.kicker')" :name="'pages.'.$slug.'.kicker'" required>
                            <x-admin.input name="pages[{{ $slug }}][kicker]" :id="'pages.'.$slug.'.kicker'" value="{{ old('pages.'.$slug.'.kicker', $page['kicker']) }}" />
                        </x-admin.field>
                        <x-admin.field :label="__('admin.site.body')" :name="'pages.'.$slug.'.p1'" required>
                            <x-admin.textarea name="pages[{{ $slug }}][p1]" :id="'pages.'.$slug.'.p1'" rows="4">{{ old('pages.'.$slug.'.p1', $page['p1']) }}</x-admin.textarea>
                        </x-admin.field>
                        <x-admin.field :label="__('admin.site.body_2')" :name="'pages.'.$slug.'.p2'">
                            <x-admin.textarea name="pages[{{ $slug }}][p2]" :id="'pages.'.$slug.'.p2'" rows="4">{{ old('pages.'.$slug.'.p2', $page['p2']) }}</x-admin.textarea>
                        </x-admin.field>
                    </div>
                </section>
            @endforeach

            <div>
                <x-admin.button type="submit" data-busy-label="{{ __('admin.common.saving') }}">
                    {{ __('admin.common.save') }}
                </x-admin.button>
            </div>
        </form>
    </div>
@endsection
