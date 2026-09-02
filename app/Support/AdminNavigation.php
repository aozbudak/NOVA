<?php

namespace App\Support;

final class AdminNavigation
{
    /**
     * @var array<string, string>
     */
    private const ROUTE_PERMISSIONS = [
        'admin.dashboard' => 'dashboard',
        'admin.products.index' => 'products',
        'admin.products.create' => 'products',
        'admin.products.store' => 'products',
        'admin.products.edit' => 'products',
        'admin.products.update' => 'products',
        'admin.categories.index' => 'categories',
        'admin.brands.index' => 'brands',
        'admin.variants.index' => 'variants',
        'admin.inventory.index' => 'inventory',
        'admin.inventory.movements' => 'inventory',
        'admin.barcode.index' => 'barcode',
        'admin.pos.index' => 'pos',
        'admin.sales.index' => 'sales',
        'admin.sales.show' => 'sales',
        'admin.returns.index' => 'returns',
        'admin.returns.create' => 'returns',
        'admin.returns.store' => 'returns',
        'admin.returns.show' => 'returns',
        'admin.exchanges.index' => 'exchanges',
        'admin.exchanges.show' => 'exchanges',
        'admin.customers.index' => 'customers',
        'admin.customers.show' => 'customers',
        'admin.suppliers.index' => 'suppliers',
        'admin.suppliers.create' => 'suppliers',
        'admin.suppliers.store' => 'suppliers',
        'admin.suppliers.show' => 'suppliers',
        'admin.cash.index' => 'cash',
        'admin.cash.movements' => 'cash',
        'admin.cash.open' => 'cash',
        'admin.cash.open.store' => 'cash',
        'admin.cash.close' => 'cash',
        'admin.cash.close.store' => 'cash',
        'admin.income-expense.index' => 'income_expense',
        'admin.income-expense.create' => 'income_expense',
        'admin.income-expense.store' => 'income_expense',
        'admin.payments.index' => 'payments',
        'admin.reports.index' => 'reports',
        'admin.reports.show' => 'reports',
        'admin.reports.export' => 'reports',
        'admin.users.index' => 'users',
        'admin.users.create' => 'users',
        'admin.users.store' => 'users',
        'admin.users.edit' => 'users',
        'admin.users.update' => 'users',
        'admin.roles.index' => 'roles',
        'admin.roles.show' => 'roles',
        'admin.audit.index' => 'audit',
        'admin.settings.index' => 'settings',
        'admin.profile.show' => 'profile',
    ];

    public function __construct(private AdminStaff $staff) {}

    public function staff(): AdminStaff
    {
        return $this->staff;
    }

    /**
     * @return list<array{label: string, items: list<array{key: string, label: string, icon: string, route: string, permission: string}>}>
     */
    public function sections(): array
    {
        $sections = [
            [
                'label' => __('admin.nav.general'),
                'items' => [
                    $this->item('dashboard', 'dashboard', 'admin.dashboard', 'dashboard'),
                ],
            ],
            [
                'label' => __('admin.nav.store'),
                'items' => [
                    $this->item('products', 'products', 'admin.products.index', 'products'),
                    $this->item('categories', 'categories', 'admin.categories.index', 'categories'),
                    $this->item('brands', 'brands', 'admin.brands.index', 'brands'),
                    $this->item('variants', 'variants', 'admin.variants.index', 'variants'),
                    $this->item('inventory', 'inventory', 'admin.inventory.index', 'inventory'),
                    $this->item('barcode', 'barcode', 'admin.barcode.index', 'barcode'),
                ],
            ],
            [
                'label' => __('admin.nav.sales_group'),
                'items' => [
                    $this->item('pos', 'pos', 'admin.pos.index', 'pos'),
                    $this->item('sales', 'sales', 'admin.sales.index', 'sales'),
                    $this->item('returns', 'returns', 'admin.returns.index', 'returns'),
                    $this->item('exchanges', 'exchanges', 'admin.exchanges.index', 'exchanges'),
                ],
            ],
            [
                'label' => __('admin.nav.people'),
                'items' => [
                    $this->item('customers', 'customers', 'admin.customers.index', 'customers'),
                    $this->item('suppliers', 'suppliers', 'admin.suppliers.index', 'suppliers'),
                ],
            ],
            [
                'label' => __('admin.nav.finance'),
                'items' => [
                    $this->item('cash', 'cash', 'admin.cash.index', 'cash'),
                    $this->item('income_expense', 'income-expense', 'admin.income-expense.index', 'income_expense'),
                    $this->item('payments', 'payments', 'admin.payments.index', 'payments'),
                ],
            ],
            [
                'label' => __('admin.nav.reporting'),
                'items' => [
                    $this->item('reports', 'reports', 'admin.reports.index', 'reports'),
                ],
            ],
            [
                'label' => __('admin.nav.management'),
                'items' => [
                    $this->item('roles', 'roles', 'admin.roles.index', 'roles'),
                    $this->item('audit', 'audit', 'admin.audit.index', 'audit'),
                ],
            ],
            [
                'label' => __('admin.nav.system'),
                'items' => [
                    $this->item('settings', 'settings', 'admin.settings.index', 'settings'),
                ],
            ],
        ];

        $visible = [];

        foreach ($sections as $section) {
            $items = array_values(array_filter(
                $section['items'],
                fn (array $item): bool => $this->staff->role->can($item['permission']),
            ));

            if ($items === []) {
                continue;
            }

            $visible[] = [
                'label' => $section['label'],
                'items' => $items,
            ];
        }

        return $visible;
    }

    public function homeRoute(): string
    {
        foreach ($this->sections() as $section) {
            foreach ($section['items'] as $item) {
                return $item['route'];
            }
        }

        return 'admin.dashboard';
    }

    public function permissionForRoute(?string $routeName): ?string
    {
        if ($routeName === null) {
            return null;
        }

        return self::ROUTE_PERMISSIONS[$routeName] ?? null;
    }

    /**
     * @return list<array{label: string, url: ?string}>
     */
    public function breadcrumbs(?string $routeName): array
    {
        if ($routeName === 'admin.dashboard') {
            return [['label' => __('admin.nav.dashboard'), 'url' => null]];
        }

        if ($routeName === 'admin.profile.show') {
            return [['label' => __('admin.nav.profile'), 'url' => null]];
        }

        $nested = $this->nestedBreadcrumbs($routeName);

        if ($nested !== []) {
            return $nested;
        }

        foreach ($this->sections() as $section) {
            foreach ($section['items'] as $item) {
                if ($item['route'] !== $routeName) {
                    continue;
                }

                $crumbs = [];

                if ($this->staff->role->can('dashboard')) {
                    $crumbs[] = [
                        'label' => __('admin.nav.dashboard'),
                        'url' => route('admin.dashboard'),
                    ];
                }

                $crumbs[] = [
                    'label' => $item['label'],
                    'url' => null,
                ];

                return $crumbs;
            }
        }

        return [];
    }

    /**
     * @return list<array{label: string, url: ?string}>
     */
    private function nestedBreadcrumbs(?string $routeName): array
    {
        $map = [
            'admin.products.create' => [
                ['label' => __('admin.nav.products'), 'url' => route('admin.products.index')],
                ['label' => __('admin.products.add'), 'url' => null],
            ],
            'admin.products.edit' => [
                ['label' => __('admin.nav.products'), 'url' => route('admin.products.index')],
                ['label' => __('admin.products.edit'), 'url' => null],
            ],
            'admin.inventory.movements' => [
                ['label' => __('admin.nav.inventory'), 'url' => route('admin.inventory.index')],
                ['label' => __('admin.inventory.movements'), 'url' => null],
            ],
            'admin.customers.show' => [
                ['label' => __('admin.nav.customers'), 'url' => route('admin.customers.index')],
                ['label' => __('admin.customers.detail'), 'url' => null],
            ],
            'admin.suppliers.create' => [
                ['label' => __('admin.nav.suppliers'), 'url' => route('admin.suppliers.index')],
                ['label' => __('admin.suppliers.add'), 'url' => null],
            ],
            'admin.suppliers.show' => [
                ['label' => __('admin.nav.suppliers'), 'url' => route('admin.suppliers.index')],
                ['label' => __('admin.suppliers.detail'), 'url' => null],
            ],
            'admin.sales.show' => [
                ['label' => __('admin.nav.sales'), 'url' => route('admin.sales.index')],
                ['label' => __('admin.sales.detail'), 'url' => null],
            ],
            'admin.returns.create' => [
                ['label' => __('admin.nav.returns'), 'url' => route('admin.returns.index')],
                ['label' => __('admin.returns.create'), 'url' => null],
            ],
            'admin.returns.show' => [
                ['label' => __('admin.nav.returns'), 'url' => route('admin.returns.index')],
                ['label' => __('admin.returns.detail'), 'url' => null],
            ],
            'admin.exchanges.show' => [
                ['label' => __('admin.nav.exchanges'), 'url' => route('admin.exchanges.index')],
                ['label' => __('admin.exchanges.detail'), 'url' => null],
            ],
            'admin.cash.movements' => [
                ['label' => __('admin.nav.cash'), 'url' => route('admin.cash.index')],
                ['label' => __('admin.cash.movements'), 'url' => null],
            ],
            'admin.cash.open' => [
                ['label' => __('admin.nav.cash'), 'url' => route('admin.cash.index')],
                ['label' => __('admin.cash.open'), 'url' => null],
            ],
            'admin.cash.close' => [
                ['label' => __('admin.nav.cash'), 'url' => route('admin.cash.index')],
                ['label' => __('admin.cash.close'), 'url' => null],
            ],
            'admin.income-expense.create' => [
                ['label' => __('admin.nav.income_expense'), 'url' => route('admin.income-expense.index')],
                ['label' => __('admin.income_expense.add'), 'url' => null],
            ],
            'admin.reports.show' => [
                ['label' => __('admin.nav.reports'), 'url' => route('admin.reports.index')],
                ['label' => __('admin.reports.detail'), 'url' => null],
            ],
            'admin.users.create' => [
                ['label' => __('admin.nav.roles'), 'url' => route('admin.roles.index')],
                ['label' => __('admin.users.add'), 'url' => null],
            ],
            'admin.users.edit' => [
                ['label' => __('admin.nav.roles'), 'url' => route('admin.roles.index')],
                ['label' => __('admin.users.edit'), 'url' => null],
            ],
            'admin.roles.show' => [
                ['label' => __('admin.nav.roles'), 'url' => route('admin.roles.index')],
                ['label' => __('admin.roles.detail'), 'url' => null],
            ],
        ];

        if ($routeName === null || ! isset($map[$routeName])) {
            return [];
        }

        $crumbs = [];

        if ($this->staff->role->can('dashboard')) {
            $crumbs[] = [
                'label' => __('admin.nav.dashboard'),
                'url' => route('admin.dashboard'),
            ];
        }

        return [...$crumbs, ...$map[$routeName]];
    }

    /**
     * @return array{key: string, label: string, icon: string, route: string, permission: string}
     */
    private function item(string $key, string $icon, string $route, string $permission): array
    {
        return [
            'key' => $key,
            'label' => __('admin.nav.'.$key),
            'icon' => $icon,
            'route' => $route,
            'permission' => $permission,
        ];
    }
}
