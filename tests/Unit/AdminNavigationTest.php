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

    public function test_store_manager_receives_roles_and_profile_items(): void
    {
        $keys = $this->itemKeys(StaffRole::StoreManager);

        $this->assertContains('dashboard', $keys);
        $this->assertContains('roles', $keys);
        $this->assertContains('site_about', $keys);
        $this->assertContains('site_help', $keys);
        $this->assertContains('site_journal', $keys);
        $this->assertContains('cash_movements', $keys);
        $this->assertContains('stock_movements', $keys);
        $this->assertContains('notifications', $keys);
        $this->assertContains('profile', $keys);
        $this->assertNotContains('users', $keys);
        $this->assertNotContains('audit', $keys);
        $this->assertNotContains('settings', $keys);
    }

    public function test_site_section_lists_footer_groups(): void
    {
        $navigation = $this->navigation(StaffRole::SuperAdmin);
        $site = collect($navigation->sections())->firstWhere('key', 'site');

        $this->assertSame('Site management', $site['label']);
        $this->assertSame(
            ['site_about', 'site_help', 'site_journal'],
            collect($site['items'])->pluck('key')->all(),
        );
    }

    public function test_system_section_holds_roles_and_footer_operations(): void
    {
        $navigation = $this->navigation(StaffRole::SuperAdmin);
        $sections = collect($navigation->sections());

        $this->assertNotContains('management', $sections->pluck('key')->all());
        $this->assertSame('system', $sections->last()['key']);
        $this->assertSame('System management', $sections->last()['label']);
        $this->assertSame(
            ['roles', 'audit', 'notifications', 'profile', 'settings'],
            collect($sections->last()['items'])->pluck('key')->all(),
        );
        $this->assertNotContains('users', $this->itemKeys(StaffRole::SuperAdmin));
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
