function initSidebar() {
    const root = document.documentElement;
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (! toggle) {
        return;
    }

    const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

    const setCollapsed = (collapsed) => {
        root.classList.toggle('sidebar-collapsed', collapsed);
        localStorage.setItem('nova.admin.sidebar', collapsed ? 'collapsed' : 'expanded');
        toggle.setAttribute('aria-label', collapsed ? toggle.dataset.expandLabel ?? '' : toggle.dataset.collapseLabel ?? '');
    };

    const setOpen = (open) => {
        root.classList.toggle('sidebar-open', open);
        backdrop?.classList.toggle('hidden', ! open);
        document.body.classList.toggle('overflow-hidden', open);
    };

    toggle.addEventListener('click', () => {
        if (isDesktop()) {
            setCollapsed(! root.classList.contains('sidebar-collapsed'));
            return;
        }

        setOpen(! root.classList.contains('sidebar-open'));
    });

    backdrop?.addEventListener('click', () => setOpen(false));

    window.addEventListener('resize', () => {
        if (isDesktop()) {
            setOpen(false);
        }
    });
}

function initDropdowns() {
    const closeAll = (except = null) => {
        document.querySelectorAll('[data-dropdown]').forEach((root) => {
            if (root === except) {
                return;
            }

            root.querySelector('[data-dropdown-panel]')?.setAttribute('hidden', '');
            root.querySelector('[data-dropdown-trigger]')?.setAttribute('aria-expanded', 'false');
        });
    };

    document.querySelectorAll('[data-dropdown]').forEach((root) => {
        const trigger = root.querySelector('[data-dropdown-trigger]');
        const panel = root.querySelector('[data-dropdown-panel]');

        if (! trigger || ! panel) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const open = panel.hasAttribute('hidden');
            closeAll(root);
            panel.toggleAttribute('hidden', ! open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    document.addEventListener('click', () => closeAll());
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });
}

function initSearch() {
    const overlay = document.querySelector('[data-admin-search]');
    const input = document.querySelector('[data-admin-search-input]');
    const empty = document.querySelector('[data-admin-search-empty]');
    const items = [...document.querySelectorAll('[data-search-item]')];

    if (! overlay || ! input) {
        return;
    }

    const open = () => {
        overlay.hidden = false;
        input.focus();
        document.body.classList.add('overflow-hidden');
    };

    const close = () => {
        overlay.hidden = true;
        input.value = '';
        items.forEach((item) => item.parentElement?.classList.remove('hidden'));
        empty?.setAttribute('hidden', '');
        document.body.classList.remove('overflow-hidden');
    };

    document.querySelectorAll('[data-open-search]').forEach((button) => {
        button.addEventListener('click', open);
    });

    overlay.querySelector('[data-close-search]')?.addEventListener('click', close);
    overlay.querySelector('[data-search-backdrop]')?.addEventListener('click', close);

    input.addEventListener('input', () => {
        const needle = input.value.trim().toLowerCase();
        let visible = 0;

        items.forEach((item) => {
            const match = (item.dataset.searchLabel ?? '').toLowerCase().includes(needle);
            item.parentElement?.classList.toggle('hidden', needle.length > 0 && ! match);
            if (needle.length === 0 || match) {
                visible += 1;
            }
        });

        empty?.toggleAttribute('hidden', visible > 0);
    });

    document.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            open();
        }

        if (event.key === 'Escape' && ! overlay.hidden) {
            close();
        }
    });
}

initSidebar();
initDropdowns();
initSearch();
initPos();
initVariantRows();
initFilterForms();
initUserAbilities();

function initUserAbilities() {
    const field = document.querySelector('[data-user-role]');
    const boxes = [...document.querySelectorAll('[data-user-ability]')];

    if (! field || boxes.length === 0) {
        return;
    }

    const defaults = JSON.parse(field.dataset.roleAbilities ?? '{}');

    field.addEventListener('change', () => {
        const typed = field.value.trim().toLowerCase();
        const key = Object.keys(defaults).find((name) => name.toLowerCase() === typed);

        if (key === undefined) {
            return;
        }

        const allowed = defaults[key];

        boxes.forEach((box) => {
            box.checked = allowed.includes(box.value);
        });
    });
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function money(value) {
    return '₺' + Math.round(value).toLocaleString('en-US');
}

function initFilterForms() {
    document.querySelectorAll('form[method="GET"] select, form[method="GET"] input[type="date"]').forEach((field) => {
        field.addEventListener('change', () => field.form?.requestSubmit());
    });
}

function initVariantRows() {
    const add = document.querySelector('[data-variant-add]');
    const list = document.querySelector('[data-variant-list]');
    const template = document.querySelector('[data-variant-template]');

    if (! add || ! list || ! template) {
        return;
    }

    add.addEventListener('click', () => {
        list.insertAdjacentHTML('beforeend', template.innerHTML);
    });
}

function initPos() {
    const root = document.querySelector('[data-pos]');

    if (! root) {
        return;
    }

    const search = root.querySelector('[data-pos-search]');
    const items = [...root.querySelectorAll('[data-pos-item]')];
    const empty = root.querySelector('[data-pos-empty]');
    const body = root.querySelector('[data-pos-cart-body]');
    const table = root.querySelector('[data-pos-cart-table]');
    const cartEmpty = root.querySelector('[data-pos-cart-empty]');
    const subtotalEl = root.querySelector('[data-pos-subtotal]');
    const discountEl = root.querySelector('[data-pos-discount]');
    const totalEl = root.querySelector('[data-pos-total]');
    /** @type {Map<string, {sku: string, name: string, variant: string, price: number, qty: number, discount: number}>} */
    const cart = new Map();

    const payload = (button) => ({
        sku: button.dataset.sku,
        name: button.dataset.name,
        variant: button.dataset.variant,
        price: Number(button.dataset.price),
        barcode: button.dataset.barcode,
    });

    const renderCart = () => {
        const lines = [...cart.values()];
        table?.classList.toggle('hidden', lines.length === 0);
        cartEmpty?.classList.toggle('hidden', lines.length > 0);

        if (! body) {
            return;
        }

        body.innerHTML = lines.map((line) => `
            <tr class="border-b border-border">
                <td class="px-3 py-2">
                    <span class="block text-foreground">${escapeHtml(line.name)}</span>
                    <span class="block text-muted-foreground">${escapeHtml(line.variant)}</span>
                </td>
                <td class="px-3 py-2">
                    <button type="button" data-pos-qty="${escapeHtml(line.sku)}" data-delta="-1" class="px-1 text-muted-foreground">−</button>
                    ${line.qty}
                    <button type="button" data-pos-qty="${escapeHtml(line.sku)}" data-delta="1" class="px-1 text-muted-foreground">+</button>
                </td>
                <td class="px-3 py-2 text-foreground">${money(line.price)}</td>
                <td class="px-3 py-2 text-muted-foreground">${money(line.discount)}</td>
                <td class="px-3 py-2 text-foreground">${money((line.price * line.qty) - line.discount)}</td>
            </tr>
        `).join('');

        const subtotal = lines.reduce((sum, line) => sum + (line.price * line.qty), 0);
        const discount = lines.reduce((sum, line) => sum + line.discount, 0);

        if (subtotalEl) {
            subtotalEl.textContent = money(subtotal);
        }
        if (discountEl) {
            discountEl.textContent = money(discount);
        }
        if (totalEl) {
            totalEl.textContent = money(subtotal - discount);
        }
    };

    const addItem = (data) => {
        const current = cart.get(data.sku);

        if (current) {
            current.qty += 1;
        } else {
            cart.set(data.sku, { ...data, qty: 1, discount: 0 });
        }

        renderCart();
    };

    const visibleItems = () => items.filter((item) => ! item.closest('li')?.classList.contains('hidden'));

    const filter = (needle) => {
        const value = needle.trim().toLowerCase();
        let shown = 0;

        items.forEach((item) => {
            const haystack = `${item.dataset.sku} ${item.dataset.barcode} ${item.dataset.name} ${item.dataset.variant}`.toLowerCase();
            const match = value === '' || haystack.includes(value);
            item.closest('li')?.classList.toggle('hidden', ! match);
            if (match) {
                shown += 1;
            }
        });

        empty?.toggleAttribute('hidden', shown > 0);
    };

    search?.addEventListener('input', () => filter(search.value));

    search?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        const needle = search.value.trim().toLowerCase();
        const exact = items.find((item) => item.dataset.barcode === needle || item.dataset.sku?.toLowerCase() === needle);
        const first = exact ?? visibleItems()[0];

        if (first) {
            addItem(payload(first));
            search.value = '';
            filter('');
        }
    });

    root.addEventListener('click', (event) => {
        const item = event.target.closest('[data-pos-item]');
        if (item) {
            addItem(payload(item));
            search?.focus();
            return;
        }

        const qty = event.target.closest('[data-pos-qty]');
        if (qty) {
            const line = cart.get(qty.dataset.posQty);
            if (! line) {
                return;
            }
            line.qty += Number(qty.dataset.delta);
            if (line.qty <= 0) {
                cart.delete(line.sku);
            }
            renderCart();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (! root.isConnected) {
            return;
        }

        if (event.key === 'F2' || event.key === 'F3' || event.key === 'F4') {
            event.preventDefault();
        }

        if (event.target instanceof HTMLInputElement && event.target !== search) {
            return;
        }

        if (event.key === '/' && document.activeElement !== search) {
            event.preventDefault();
            search?.focus();
        }
    });

    search?.focus();
}
