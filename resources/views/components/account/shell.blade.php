@props([
    'title',
    'kicker' => null,
])

<div class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute top-10 left-[8%] size-72 rounded-full bg-foreground/6 blur-3xl"></div>
        <div class="absolute right-[10%] bottom-8 size-80 rounded-full bg-foreground/5 blur-3xl"></div>
    </div>

    <div class="relative mx-auto grid max-w-[1200px] gap-8 px-4 py-10 md:grid-cols-[17rem_minmax(0,1fr)] md:gap-12 md:px-8 md:py-16">
        <x-account.nav />

        <div class="min-w-0">
            <header class="mb-8 flex flex-col gap-2 md:mb-10">
                @if (filled($kicker))
                    <p class="text-[11px] tracking-nav uppercase text-muted-foreground">{{ $kicker }}</p>
                @endif
                <h1 class="font-serif text-3xl md:text-4xl">{{ $title }}</h1>
                @isset($intro)
                    <div class="max-w-xl text-sm leading-relaxed text-muted-foreground">{{ $intro }}</div>
                @endisset
            </header>

            {{ $slot }}
        </div>
    </div>
</div>
