<?php

namespace Tests\Unit;

use App\Enums\StaffRole;
use App\Support\AdminNavigation;
use App\Support\AdminStaff;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    public function test_cashier_only_receives_pos_sales_customer_and_return_items(): void
    {
        $keys = $this->itemKeys(StaffRole::Cashier);

        $this->assertSame(['pos', 'sales', 'returns', 'customers', 'notifications', 'profile'], $keys);
        $this->assertSame('admin.pos.index', $this->navigation(StaffRole::Cashier)->homeRoute());
    }

    public function test_warehouse_staff_only_receives_catalog_and_stock_items(): void
    {
        $this->assertSame(
            ['products', 'inventory', 'stock_movements', 'barcode', 'notifications', 'profile'],
            $this->itemKeys(StaffRole::WarehouseStaff),
        );
    }

    public function test_store_manager_receives_users_roles_and_profile_items(): void
    {
        $keys = $this->itemKeys(StaffRole::StoreManager);

        $this->assertContains('dashboard', $keys);
        $this->assertContains('users', $keys);
        $this->assertContains('roles', $keys);
        $this->assertContains('cash_movements', $keys);
        $this->assertContains('stock_movements', $keys);
        $this->assertContains('notifications', $keys);
        $this->assertContains('profile', $keys);
        $this->assertNotContains('audit', $keys);
        $this->assertNotContains('settings', $keys);
    }

    /**
     * @return list<string>
     */
    private function itemKeys(StaffRole $role): array
    {
        return collect($this->navigation($role)->sections())
            ->flatMap(fn (array $section) => $section['items'])
            ->pluck('key')
            ->all();
    }

    private function navigation(StaffRole $role): AdminNavigation
    {
        return new AdminNavigation(new AdminStaff('Ada Lovelace', 'ada@nova.store', $role));
    }
}
