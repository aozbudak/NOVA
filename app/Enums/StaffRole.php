<?php

namespace App\Enums;

enum StaffRole: string
{
    case SuperAdmin = 'super_admin';
    case StoreManager = 'store_manager';
    case Cashier = 'cashier';
    case WarehouseStaff = 'warehouse_staff';

    /**
     * @return list<string>
     */
    public static function abilityGroups(): array
    {
        return [
            'products',
            'inventory',
            'customers',
            'suppliers',
            'sales',
            'cash',
            'returns',
            'reports',
            'users',
            'audit',
            'settings',
        ];
    }

    /**
     * @return list<string>
     */
    public static function abilityActions(): array
    {
        return ['view', 'create', 'edit', 'delete'];
    }

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => __('admin.roles.super_admin'),
            self::StoreManager => __('admin.roles.store_manager'),
            self::Cashier => __('admin.roles.cashier'),
            self::WarehouseStaff => __('admin.roles.warehouse_staff'),
        };
    }

    /**
     * @return list<string>
     */
    public static function operations(): array
    {
        return [
            'products',
            'categories',
            'brands',
            'variants',
            'inventory',
            'barcode',
            'pos',
            'sales',
            'returns',
            'exchanges',
            'customers',
            'suppliers',
            'cash',
            'income_expense',
            'payments',
            'reports',
            'users',
            'roles',
            'audit',
            'settings',
        ];
    }

    /**
     * @return list<string>
     */
    public function assignedOperations(): array
    {
        if (in_array('*', $this->permissions(), true)) {
            return self::operations();
        }

        return array_values(array_intersect($this->permissions(), self::operations()));
    }

    /**
     * @return list<string>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => ['*'],
            self::StoreManager => [
                'dashboard',
                'products',
                'categories',
                'brands',
                'variants',
                'inventory',
                'barcode',
                'pos',
                'sales',
                'returns',
                'exchanges',
                'customers',
                'suppliers',
                'cash',
                'income_expense',
                'payments',
                'reports',
                'users',
                'roles',
            ],
            self::Cashier => [
                'pos',
                'sales',
                'customers',
                'returns',
            ],
            self::WarehouseStaff => [
                'products',
                'inventory',
                'barcode',
            ],
        };
    }

    /**
     * @return array<string, array{view: bool, create: bool, edit: bool, delete: bool}>
     */
    public function matrix(): array
    {
        $denied = ['view' => false, 'create' => false, 'edit' => false, 'delete' => false];
        $read = ['view' => true, 'create' => false, 'edit' => false, 'delete' => false];
        $write = ['view' => true, 'create' => true, 'edit' => true, 'delete' => false];
        $full = ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];

        return match ($this) {
            self::SuperAdmin => collect(self::abilityGroups())
                ->mapWithKeys(fn (string $group): array => [$group => $full])
                ->all(),
            self::StoreManager => [
                'products' => $write,
                'inventory' => $write,
                'customers' => $write,
                'suppliers' => $write,
                'sales' => $read,
                'cash' => $write,
                'returns' => $write,
                'reports' => $read,
                'users' => $write,
                'audit' => $denied,
                'settings' => $denied,
            ],
            self::Cashier => [
                'products' => $denied,
                'inventory' => $denied,
                'customers' => $write,
                'suppliers' => $denied,
                'sales' => $read,
                'cash' => $denied,
                'returns' => ['view' => true, 'create' => true, 'edit' => false, 'delete' => false],
                'reports' => $denied,
                'users' => $denied,
                'audit' => $denied,
                'settings' => $denied,
            ],
            self::WarehouseStaff => [
                'products' => $write,
                'inventory' => $write,
                'customers' => $denied,
                'suppliers' => $denied,
                'sales' => $denied,
                'cash' => $denied,
                'returns' => $denied,
                'reports' => $denied,
                'users' => $denied,
                'audit' => $denied,
                'settings' => $denied,
            ],
        };
    }

    public function allows(string $group, string $action): bool
    {
        return $this->matrix()[$group][$action] ?? false;
    }

    public function can(string $permission): bool
    {
        $permissions = $this->permissions();

        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }
}
