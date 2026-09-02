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
        'admin.categories.index' => 'categories',
        'admin.brands.index' => 'brands',
        'admin.variants.index' => 'variants',
        'admin.inventory.index' => 'inventory',
        'admin.barcode.index' => 'barcode',
        'admin.pos.index' => 'pos',
        'admin.sales.index' => 'sales',
        'admin.returns.index' => 'returns',
        'admin.exchanges.index' => 'exchanges',
        'admin.customers.index' => 'customers',
        'admin.suppliers.index' => 'suppliers',
        'admin.cash.index' => 'cash',
        'admin.income-expense.index' => 'income_expense',
        'admin.payments.index' => 'payments',
        'admin.reports.sales' => 'reports_sales',
        'admin.reports.inventory' => 'reports_inventory',
        'admin.reports.cash' => 'reports_cash',
        'admin.users.index' => 'users',
        'admin.roles.index' => 'roles',
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
                    $this->item('reports_sales', 'reports', 'admin.reports.sales', 'reports_sales'),
                    $this->item('reports_inventory', 'inventory', 'admin.reports.inventory', 'reports_inventory'),
                    $this->item('reports_cash', 'cash', 'admin.reports.cash', 'reports_cash'),
                ],
            ],
            [
                'label' => __('admin.nav.management'),
                'items' => [
                    $this->item('users', 'users', 'admin.users.index', 'users'),
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
