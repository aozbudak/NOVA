@if ($paginator->hasPages())
    <div class="flex flex-wrap items-center gap-1">
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-[12px] text-muted-foreground">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    <a
                        href="{{ $url }}"
                        @class([
                            'inline-flex size-8 items-center justify-center rounded-xl text-[12px]',
                            'bg-foreground text-background' => $page == $paginator->currentPage(),
                            'text-muted-foreground hover:bg-muted hover:text-foreground' => $page != $paginator->currentPage(),
                        ])
                        @if ($page == $paginator->currentPage()) aria-current="page" @endif
                    >{{ $page }}</a>
                @endforeach
            @endif
        @endforeach
    </div>
@endif
