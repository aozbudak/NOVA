<footer class="relative mt-24 overflow-hidden border-t border-glass-border">
    <div class="pointer-events-none absolute inset-0" aria-hidden="true">
        <div class="absolute -top-20 left-[15%] size-72 rounded-full bg-foreground/7 blur-3xl"></div>
        <div class="absolute right-[12%] -bottom-16 size-80 rounded-full bg-foreground/5 blur-3xl"></div>
    </div>
    <div class="pointer-events-none absolute inset-0 glass-panel"></div>
    <div class="relative mx-auto grid max-w-[1600px] gap-12 px-4 py-20 md:grid-cols-2 md:px-8 lg:grid-cols-6">
        <div class="flex flex-col gap-5 md:col-span-2">
            <x-logo />
            <p class="max-w-xs text-sm leading-relaxed text-muted-foreground">
                {{ __('storefront.footer.tagline') }}
            </p>
            <div class="flex gap-4">
                <a href="https://instagram.com" class="text-foreground/80 transition-opacity hover:opacity-60" aria-label="{{ __('storefront.footer.instagram') }}" rel="noopener noreferrer">
                    <x-icon name="instagram" />
                </a>
                <a href="https://pinterest.com" class="text-foreground/80 transition-opacity hover:opacity-60" aria-label="{{ __('storefront.footer.pinterest') }}" rel="noopener noreferrer">
                    <x-icon name="pinterest" />
                </a>
                <a href="https://x.com" class="text-foreground/80 transition-opacity hover:opacity-60" aria-label="{{ __('storefront.footer.x') }}" rel="noopener noreferrer">
                    <x-icon name="x-social" />
                </a>
            </div>
        </div>
        <div>
            <p class="text-[11px] font-medium tracking-nav uppercase">{{ __('storefront.footer.shop') }}</p>
            <ul class="mt-5 flex flex-col gap-2.5 text-sm text-muted-foreground">
                <li><a href="{{ route('shop.show', 'women') }}" class="transition-colors hover:text-foreground">{{ __('storefront.nav.women') }}</a></li>
                <li><a href="{{ route('shop.show', 'men') }}" class="transition-colors hover:text-foreground">{{ __('storefront.nav.men') }}</a></li>
                <li><a href="{{ route('shop.show', 'kids') }}" class="transition-colors hover:text-foreground">{{ __('storefront.nav.kids') }}</a></li>
                <li><a href="{{ route('shop.show', 'sport') }}" class="transition-colors hover:text-foreground">{{ __('storefront.nav.sport') }}</a></li>
                <li><a href="{{ route('shop.show', 'new-in') }}" class="transition-colors hover:text-foreground">{{ __('storefront.nav.new-in') }}</a></li>
                <li><a href="{{ route('shop.show', 'collections') }}" class="transition-colors hover:text-foreground">{{ __('storefront.nav.collections') }}</a></li>
                <li><a href="{{ route('shop.show', 'sale') }}" class="transition-colors hover:text-foreground">{{ __('storefront.nav.sale') }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-[11px] font-medium tracking-nav uppercase">{{ __('storefront.footer.help') }}</p>
            <ul class="mt-5 flex flex-col gap-2.5 text-sm text-muted-foreground">
                <li><a href="{{ route('pages.show', 'contact') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.contact') }}</a></li>
                <li><a href="{{ route('pages.show', 'shipping') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.shipping') }}</a></li>
                <li><a href="{{ route('pages.show', 'returns') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.returns') }}</a></li>
                <li><a href="{{ route('pages.show', 'faq') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.faq') }}</a></li>
                <li><a href="{{ route('pages.show', 'size-guide') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.size_guide') }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-[11px] font-medium tracking-nav uppercase">{{ __('storefront.footer.about') }}</p>
            <ul class="mt-5 flex flex-col gap-2.5 text-sm text-muted-foreground">
                <li><a href="{{ route('pages.show', 'about') }}" class="transition-colors hover:text-foreground">{{ __('storefront.pages.about.title') }}</a></li>
                <li><a href="{{ route('pages.show', 'careers') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.careers') }}</a></li>
                <li><a href="{{ route('pages.show', 'sustainability') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.sustainability') }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-[11px] font-medium tracking-nav uppercase">{{ __('storefront.footer.journal') }}</p>
            <p class="mt-5 text-sm leading-relaxed text-muted-foreground">{{ __('storefront.footer.journal_text') }}</p>
            <form action="{{ route('pages.show', 'contact') }}" method="get" class="mt-5 flex items-end gap-3 border-b border-glass-border pb-2">
                <label class="sr-only" for="footer-email">{{ __('storefront.footer.email') }}</label>
                <input
                    id="footer-email"
                    type="email"
                    name="email"
                    required
                    placeholder="{{ __('storefront.footer.email') }}"
                    class="w-full bg-transparent py-1 text-sm outline-none placeholder:text-muted-foreground"
                >
                <button type="submit" class="shrink-0 pb-1 text-[11px] tracking-nav uppercase">{{ __('storefront.footer.subscribe') }}</button>
            </form>
            <ul class="mt-8 flex flex-col gap-2.5 text-sm text-muted-foreground">
                <li><a href="{{ route('pages.show', 'privacy') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.privacy') }}</a></li>
                <li><a href="{{ route('pages.show', 'terms') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.terms') }}</a></li>
                <li><a href="{{ route('pages.show', 'cookies') }}" class="transition-colors hover:text-foreground">{{ __('storefront.footer.cookies') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="relative border-t border-glass-border">
        <div class="mx-auto flex max-w-[1600px] flex-col items-start justify-between gap-3 px-4 py-6 text-xs text-muted-foreground md:flex-row md:items-center md:px-8">
            <p>{{ __('storefront.footer.copyright') }}</p>
            <p class="tracking-label uppercase">{{ __('storefront.footer.crafted') }}</p>
        </div>
    </div>
</footer>
