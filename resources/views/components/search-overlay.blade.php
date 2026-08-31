<div data-overlay="search" class="glass-strong fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="search-title">
    <div class="mx-auto flex h-full max-w-3xl flex-col px-4 py-8 md:px-8">
        <div class="flex items-start justify-between gap-6">
            <h2 id="search-title" class="font-serif text-3xl tracking-tight md:text-4xl">{{ __('storefront.search.prompt') }}</h2>
            <button type="button" data-close="search" aria-label="{{ __('storefront.search.close') }}" class="p-1">
                <x-icon name="x" />
            </button>
        </div>
        <form action="{{ route('search') }}" method="get" class="mt-10 border-b border-foreground pb-3">
            <label for="search-input" class="sr-only">{{ __('storefront.search.title') }}</label>
            <input
                id="search-input"
                name="q"
                type="search"
                autocomplete="off"
                data-search-input
                placeholder="{{ __('storefront.search.placeholder') }}"
                class="w-full bg-transparent text-xl outline-none placeholder:text-muted-foreground md:text-2xl"
            >
        </form>
        <div data-search-results class="mt-8 hidden"></div>
        <div data-search-trending class="mt-10">
            <p class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ __('storefront.search.trending') }}</p>
            <ul class="mt-4 flex flex-col gap-3">
                @foreach ([
                    __('storefront.search.dresses') => 'Dresses',
                    __('storefront.search.blazers') => 'Blazers',
                    __('storefront.search.denim') => 'Denim',
                    __('storefront.search.new_collection') => 'New Collection',
                ] as $label => $term)
                    <li>
                        <a href="{{ route('search', ['q' => $term]) }}" class="text-sm tracking-wide uppercase">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
