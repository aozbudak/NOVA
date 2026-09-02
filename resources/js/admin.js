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
