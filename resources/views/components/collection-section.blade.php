@props(['campaigns'])

<section class="mx-auto mt-4 max-w-[1600px] px-4 md:px-8">
    <div class="grid gap-4 md:grid-cols-2">
        @foreach (array_slice($campaigns, 0, 2) as $campaign)
            <a href="{{ $campaign['href'] }}" class="group relative block min-h-[70vh] overflow-hidden bg-muted">
                <img
                    src="{{ $campaign['image'] }}"
                    alt="{{ $campaign['title'] }} collection"
                    width="1200"
                    height="1600"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                    loading="lazy"
                >
                <div class="absolute inset-0 bg-foreground/15"></div>
                <div class="absolute inset-x-0 bottom-0 p-6 text-primary-foreground md:p-8">
                    <p class="font-serif text-4xl md:text-5xl">{{ $campaign['title'] }}</p>
                    <p class="mt-3 inline-flex items-center gap-2 text-[11px] tracking-nav uppercase">
                        Shop collection
                        <x-icon name="arrow-right" size="size-4" />
                    </p>
                </div>
            </a>
        @endforeach
    </div>
    @if (isset($campaigns[2]))
        <a href="{{ $campaigns[2]['href'] }}" class="group relative mt-4 block min-h-[50vh] overflow-hidden bg-muted md:min-h-[62vh]">
            <img
                src="{{ $campaigns[2]['image'] }}"
                alt="{{ $campaigns[2]['title'] }}"
                width="2000"
                height="1100"
                class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                loading="lazy"
            >
            <div class="absolute inset-0 bg-foreground/20"></div>
            <div class="absolute inset-x-0 bottom-0 p-6 text-primary-foreground md:p-10">
                <p class="text-[11px] tracking-nav uppercase">{{ $campaigns[2]['eyebrow'] }}</p>
                <p class="mt-2 font-serif text-4xl md:text-6xl">{{ $campaigns[2]['title'] }}</p>
                <p class="mt-3 inline-flex items-center gap-2 text-[11px] tracking-nav uppercase">
                    Shop collection
                    <x-icon name="arrow-right" size="size-4" />
                </p>
            </div>
        </a>
    @endif
</section>
