@props(['title'])

<header {{ $attributes->merge(['class' => 'flex flex-col gap-1']) }}>
    <h1 class="font-serif text-3xl tracking-tight md:text-4xl">{{ $title }}</h1>
    @isset($intro)
        <p class="max-w-xl text-sm leading-relaxed text-muted-foreground">{{ $intro }}</p>
    @endisset
</header>
