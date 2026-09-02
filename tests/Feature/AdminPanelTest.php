<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_dashboard_renders_operations_shell_for_super_admin(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('data-panel', false);
        $response->assertSee('NOVA');
        $response->assertSee('Admin');
        $response->assertSee('Ayşe Yılmaz');
        $response->assertSee('Super Admin');
        $response->assertSee('Today’s sales');
        $response->assertSee('NV-10482');
        $response->assertSee('Audit log');
        $response->assertSee('Roles & permissions');
    }

    public function test_cashier_is_redirected_from_dashboard_to_pos(): void
    {
        $response = $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.pos.index'));
    }

    public function test_cashier_sidebar_omits_modules_outside_the_role(): void
    {
        $response = $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.pos.index'));

        $response->assertOk();
        $response->assertSee('POS');
        $response->assertSee('Customers');
        $response->assertDontSee('Audit log');
        $response->assertDontSee('>Users<', false);
        $response->assertDontSee('>Products<', false);
    }

    public function test_cashier_is_forbidden_from_users(): void
    {
        $response = $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_warehouse_staff_can_open_inventory_and_not_pos(): void
    {
        $this->withSession(['admin.role' => StaffRole::WarehouseStaff->value])
            ->get(route('admin.inventory.index'))
            ->assertOk()
            ->assertSee('Inventory');

        $this->withSession(['admin.role' => StaffRole::WarehouseStaff->value])
            ->get(route('admin.pos.index'))
            ->assertForbidden();
    }

    public function test_products_page_renders_breadcrumb(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Products');
    }
}
