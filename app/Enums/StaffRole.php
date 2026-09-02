<?php

namespace App\Enums;

enum StaffRole: string
{
    case SuperAdmin = 'super_admin';
    case StoreManager = 'store_manager';
    case Cashier = 'cashier';
    case WarehouseStaff = 'warehouse_staff';

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

    public function can(string $permission): bool
    {
        $permissions = $this->permissions();

        if (in_array('*', $permissions, true)) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }
}
