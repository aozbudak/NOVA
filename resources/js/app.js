const nova = () => window.NOVA ?? {};

const fillRoute = (template, token, value) => String(template ?? '').replace(token, encodeURIComponent(value));

const t = (key, fallback) => nova().i18n?.[key] ?? fallback;

const escapeHtml = (value) => String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');

const headers = (json = true) => ({
    Accept: json ? 'application/json' : 'text/html',
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': nova().csrf,
    'X-Requested-With': 'XMLHttpRequest',
});

function toast(message) {
    const root = document.getElementById('toast');
    const text = root?.querySelector('[data-toast-message]');

    if (! root || ! text) {
        return;
    }

    text.textContent = message;
    root.hidden = false;
    requestAnimationFrame(() => root.classList.remove('opacity-0'));
    window.clearTimeout(root._timer);
    root._timer = window.setTimeout(() => {
        root.classList.add('opacity-0');
        window.setTimeout(() => {
            root.hidden = true;
        }, 200);
    }, 1800);
}

function setCartCount(count) {
    document.body.dataset.cartCount = String(count);
    document.querySelectorAll('[data-cart-badge]').forEach((badge) => {
        badge.textContent = String(count);
        badge.classList.toggle('hidden', count < 1);
    });
}

async function refreshCartPanel() {
    const panel = document.querySelector('[data-cart-panel]');

    if (! panel) {
        return;
    }

    const response = await fetch(nova().routes.cartPanel, {
        headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
    });

    panel.innerHTML = await response.text();
}

function openLayer(name) {
    const drawer = document.querySelector(`[data-drawer="${name}"]`);
    const overlay = document.querySelector(`[data-overlay="${name}"]`);
    const modal = document.querySelector(`[data-modal="${name}"]`);
    const root = drawer ?? overlay ?? modal;

    if (! root) {
        return;
    }

    root.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(() => {
        root.querySelector('[data-drawer-backdrop]')?.classList.add('opacity-100');
        root.querySelector('[data-drawer-panel]')?.classList.remove('translate-x-full', '-translate-x-full');
    });

    const focusTarget = root.querySelector('input, button');
    focusTarget?.focus();
}

function closeLayer(name) {
    const root = document.querySelector(`[data-drawer="${name}"], [data-overlay="${name}"], [data-modal="${name}"]`);

    if (! root) {
        return;
    }

    root.querySelector('[data-drawer-backdrop]')?.classList.remove('opacity-100');
    const panel = root.querySelector('[data-drawer-panel]');

    if (panel) {
        panel.classList.add(panel.classList.contains('right-0') || panel.className.includes('right-0') ? 'translate-x-full' : '-translate-x-full');
    }

    window.setTimeout(() => {
        root.classList.add('hidden');
        if (! document.querySelector('[data-drawer]:not(.hidden), [data-overlay]:not(.hidden), [data-modal]:not(.hidden)')) {
            document.body.classList.remove('overflow-hidden');
        }
    }, 180);
}

function closeAllLayers() {
    document.querySelectorAll('[data-drawer], [data-overlay], [data-modal]').forEach((node) => {
        const name = node.dataset.drawer || node.dataset.overlay || node.dataset.modal;
        if (name && ! node.classList.contains('hidden')) {
            closeLayer(name);
        }
    });
}

async function addToCart(productId, size, quantity = 1) {
    const response = await fetch(nova().routes.cart, {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({ product_id: Number(productId), size, quantity }),
    });

    if (! response.ok) {
        toast(t('error', 'Something went wrong. Please try again.'));
        return;
    }

    const data = await response.json();
    setCartCount(data.count);
    await refreshCartPanel();
    toast(data.message ?? t('addedToBag', 'Added to bag'));
}

async function toggleWishlist(button) {
    const productId = Number(button.dataset.productId);
    const response = await fetch(nova().routes.wishlist, {
        method: 'POST',
        headers: headers(),
        body: JSON.stringify({ product_id: productId }),
    });

    if (! response.ok) {
        toast(t('error', 'Something went wrong. Please try again.'));
        return;
    }

    const data = await response.json();
    const added = Boolean(data.added);

    document.querySelectorAll(`[data-wishlist-toggle][data-product-id="${productId}"]`).forEach((node) => {
        node.setAttribute('aria-pressed', added ? 'true' : 'false');
        node.setAttribute('aria-label', added ? t('removeFromWishlist', 'Remove from wishlist') : t('addToWishlist', 'Add to wishlist'));
        const icon = node.querySelector('svg');
        if (icon) {
            icon.setAttribute('fill', added ? 'currentColor' : 'none');
        }
        node.classList.add('scale-110');
        window.setTimeout(() => node.classList.remove('scale-110'), 160);
    });
}

let searchTimer = 0;
let searchAbort = null;

function localSearchMatches(needle) {
    const catalog = nova().catalog ?? [];
    const query = needle.toLowerCase();

    return catalog
        .filter((item) => `${item.name} ${item.category ?? ''}`.toLowerCase().includes(query))
        .slice(0, 8);
}

function paintSearchResults(matches) {
    const results = document.querySelector('[data-search-results]');
    const trending = document.querySelector('[data-search-trending]');

    if (! results || ! trending) {
        return;
    }

    trending.classList.add('hidden');
    results.classList.remove('hidden');

    if (matches.length === 0) {
        results.innerHTML = `<p class="text-sm text-muted-foreground">${escapeHtml(t('noResults', 'No results'))}</p>`;
        return;
    }

    results.innerHTML = `<ul class="flex flex-col gap-4">${matches.map((item) => {
        const formatter = new Intl.NumberFormat(nova().locale ?? 'en', {
            style: 'currency',
            currency: item.currency ?? 'EUR',
        });

        return `
            <li>
                <a href="${fillRoute(nova().routes.product, '__SLUG__', item.slug)}" class="flex items-center gap-4">
                    <img src="${item.image}" alt="${escapeHtml(item.name)}" width="56" height="70" class="h-[70px] w-14 object-cover" loading="lazy">
                    <span>
                        <span class="block text-sm">${escapeHtml(item.name)}</span>
                        <span class="block text-xs text-muted-foreground">${formatter.format(item.price)}</span>
                    </span>
                </a>
            </li>`;
    }).join('')}</ul>`;
}

function renderSearchResults(query) {
    const results = document.querySelector('[data-search-results]');
    const trending = document.querySelector('[data-search-trending]');
    const needle = query.trim();

    if (! results || ! trending) {
        return;
    }

    if (needle.length < 2) {
        searchAbort?.abort();
        results.classList.add('hidden');
        trending.classList.remove('hidden');
        return;
    }

    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => querySearch(needle), 180);
}

async function querySearch(needle) {
    const url = nova().routes.search;

    if (! url) {
        paintSearchResults(localSearchMatches(needle));
        return;
    }

    searchAbort?.abort();
    searchAbort = new AbortController();

    try {
        const response = await fetch(`${url}?q=${encodeURIComponent(needle)}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: searchAbort.signal,
        });

        if (! response.ok) {
            throw new Error('search');
        }

        const payload = await response.json();
        const matches = Array.isArray(payload) ? payload : (payload.data ?? []);
        paintSearchResults(matches);
    } catch (error) {
        if (error.name === 'AbortError') {
            return;
        }

        paintSearchResults(localSearchMatches(needle));
    }
}

function initTheme() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            const next = isDark ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            document.documentElement.classList.toggle('dark', next === 'dark');
            document.documentElement.classList.toggle('light', next === 'light');
        });
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
        if (localStorage.getItem('theme')) {
            return;
        }

        document.documentElement.classList.toggle('dark', event.matches);
        document.documentElement.classList.remove('light');
    });
}

let applyChrome = () => {};

function initHeader() {
    const chrome = document.querySelector('[data-chrome]');

    if (! chrome) {
        return;
    }

    const overHero = chrome.dataset.overHero === 'true';

    applyChrome = () => {
        const megaOpen = chrome.classList.contains('mega-open');
        const glass = megaOpen || ! overHero || window.scrollY > 24;

        chrome.classList.toggle('text-overlay', ! glass);
        chrome.classList.toggle('text-foreground', glass);
        chrome.classList.toggle('glass', glass);
        chrome.classList.toggle('border-transparent', ! glass);
    };

    applyChrome();
    window.addEventListener('scroll', applyChrome, { passive: true });
}

function initMegaMenu() {
    const chrome = document.querySelector('[data-chrome]');
    const root = document.querySelector('[data-mega-root]');

    if (! chrome || ! root) {
        return;
    }

    const triggers = [...chrome.querySelectorAll('[data-mega-trigger]')];
    const panels = [...root.querySelectorAll('[data-mega]')];
    let closeTimer = 0;
    let current = null;

    const setExpanded = (name) => {
        triggers.forEach((trigger) => {
            trigger.setAttribute('aria-expanded', trigger.dataset.megaTrigger === name ? 'true' : 'false');
        });
    };

    const open = (name) => {
        window.clearTimeout(closeTimer);
        current = name;
        chrome.classList.add('mega-open');
        root.classList.remove('hidden');
        panels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.dataset.mega !== name);
        });
        setExpanded(name);
        applyChrome();
    };

    const close = () => {
        current = null;
        chrome.classList.remove('mega-open');
        root.classList.add('hidden');
        panels.forEach((panel) => panel.classList.add('hidden'));
        setExpanded(null);
        applyChrome();
    };

    const scheduleClose = () => {
        window.clearTimeout(closeTimer);
        closeTimer = window.setTimeout(close, 140);
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('mouseenter', () => open(trigger.dataset.megaTrigger));
        trigger.addEventListener('focus', () => open(trigger.dataset.megaTrigger));
        trigger.addEventListener('mouseleave', scheduleClose);
    });

    root.addEventListener('mouseenter', () => window.clearTimeout(closeTimer));
    root.addEventListener('mouseleave', scheduleClose);

    chrome.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && current) {
            close();
        }
    });

    window.NOVA_CLOSE_MEGA = close;
}

function initGallery() {
    document.querySelectorAll('[data-gallery]').forEach((gallery) => {
        const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');
        const track = gallery.querySelector('[data-gallery-track]');

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const index = Number(thumb.dataset.galleryThumb);
                thumbs.forEach((item) => item.classList.remove('border-foreground'));
                thumb.classList.add('border-foreground');
                const slide = track?.children[index];
                slide?.scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
            });
        });
    });
}

let galleryImages = [];
let galleryIndex = 0;

function openImageViewer(images, index) {
    galleryImages = images;
    galleryIndex = index;
    const img = document.querySelector('[data-modal-image]');

    if (img) {
        img.src = galleryImages[galleryIndex];
        img.alt = t('productImage', 'Product image');
    }

    openLayer('image');
}

function stepGallery(delta) {
    if (galleryImages.length === 0) {
        return;
    }

    galleryIndex = (galleryIndex + delta + galleryImages.length) % galleryImages.length;
    const img = document.querySelector('[data-modal-image]');

    if (img) {
        img.src = galleryImages[galleryIndex];
    }
}

document.addEventListener('click', async (event) => {
    const open = event.target.closest('[data-open]');
    if (open) {
        if (open.dataset.open === 'cart') {
            await refreshCartPanel();
        }
        openLayer(open.dataset.open);
        return;
    }

    const close = event.target.closest('[data-close]');
    if (close) {
        closeLayer(close.dataset.close);
        return;
    }

    if (event.target.closest('[data-drawer-backdrop]')) {
        const drawer = event.target.closest('[data-drawer]');
        if (drawer) {
            closeLayer(drawer.dataset.drawer);
        }
        return;
    }

    const quickAdd = event.target.closest('[data-quick-add]');
    if (quickAdd) {
        event.preventDefault();
        await addToCart(quickAdd.dataset.productId, quickAdd.dataset.size);
        openLayer('cart');
        return;
    }

    const wishlist = event.target.closest('[data-wishlist-toggle]');
    if (wishlist) {
        event.preventDefault();
        await toggleWishlist(wishlist);
        return;
    }

    const qty = event.target.closest('[data-cart-qty]');
    if (qty) {
        const quantity = Number(qty.dataset.qty);
        await fetch(fillRoute(nova().routes.cartUpdate, '__KEY__', qty.dataset.cartQty), {
            method: 'PATCH',
            headers: headers(),
            body: JSON.stringify({ quantity }),
        }).then(async (response) => {
            if (! response.ok) {
                toast(t('error', 'Something went wrong. Please try again.'));
                return;
            }
            const data = await response.json();
            setCartCount(data.count);
            await refreshCartPanel();
        });
        return;
    }

    const remove = event.target.closest('[data-cart-remove]');
    if (remove) {
        await fetch(fillRoute(nova().routes.cartDestroy ?? nova().routes.cartUpdate, '__KEY__', remove.dataset.cartRemove), {
            method: 'DELETE',
            headers: headers(),
        }).then(async (response) => {
            if (! response.ok) {
                toast(t('error', 'Something went wrong. Please try again.'));
                return;
            }
            const data = await response.json();
            setCartCount(data.count);
            await refreshCartPanel();
        });
        return;
    }

    const galleryOpen = event.target.closest('[data-gallery-open]');
    if (galleryOpen) {
        const gallery = galleryOpen.closest('[data-gallery]');
        const images = [...gallery.querySelectorAll('[data-gallery-track] img')].map((img) => img.src);
        openImageViewer(images, Number(galleryOpen.dataset.galleryOpen));
    }

    if (event.target.closest('[data-gallery-prev]')) {
        stepGallery(-1);
    }

    if (event.target.closest('[data-gallery-next]')) {
        stepGallery(1);
    }
});

document.addEventListener('submit', async (event) => {
    const checkout = event.target.closest('[data-checkout]');

    if (checkout && nova().routes.checkout && checkout.dataset.native !== 'true') {
        event.preventDefault();

        const payload = Object.fromEntries(new FormData(checkout).entries());
        delete payload._token;

        const response = await fetch(nova().routes.checkout, {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify(payload),
        });

        if (response.status === 422) {
            checkout.dataset.native = 'true';
            checkout.submit();
            return;
        }

        if (! response.ok) {
            toast(t('error', 'Something went wrong. Please try again.'));
            return;
        }

        const data = await response.json();
        window.location.href = data.confirmation_url ?? nova().routes.checkoutConfirmation;
        return;
    }

    const form = event.target.closest('[data-add-to-cart]');

    if (! form) {
        return;
    }

    event.preventDefault();
    const data = new FormData(form);
    await addToCart(data.get('product_id'), data.get('size'), Number(data.get('quantity') ?? 1));
    openLayer('cart');
});

document.addEventListener('input', (event) => {
    if (event.target.matches('[data-search-input]')) {
        renderSearchResults(event.target.value);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeAllLayers();
        window.NOVA_CLOSE_MEGA?.();
    }

    if (event.key === 'ArrowLeft') {
        stepGallery(-1);
    }

    if (event.key === 'ArrowRight') {
        stepGallery(1);
    }
});

initTheme();
initHeader();
initMegaMenu();
initGallery();

const flashStatus = document.getElementById('toast')?.dataset.flashStatus?.trim();

if (flashStatus) {
    toast(flashStatus);
}
