<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    public function test_dashboard_renders_operations_shell_for_super_admin(): void
    {
        $this->travelTo(Carbon::parse('2026-09-02 09:15:00'));

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('data-panel', false);
        $response->assertSee('Good morning, Ayşe');
        $response->assertSee('Wednesday, September 2');
        $response->assertSee('₺48,250');
        $response->assertSee('126');
        $response->assertSee('Sales overview');
        $response->assertSee('Top products');
        $response->assertSee('Inventory alert');
        $response->assertSee('Cash summary');
        $response->assertSee('Audit log');
        $response->assertSee('Roles & permissions');
        $response->assertSee('data-dashboard-skeleton', false);
        $response->assertSee('data-admin-layer="confirm"', false);
        $response->assertSee('id="admin-toast"', false);
        $response->assertSee('Processing...');
        $response->assertSee('Something went wrong.');
        $response->assertSee('Please try again.');
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
