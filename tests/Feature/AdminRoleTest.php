<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    public function test_roles_index_lists_each_staff_role(): void
    {
        $response = $this->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('Roles & permissions');
        $response->assertSee('Super Admin');
        $response->assertSee('Store Manager');
        $response->assertSee('Cashier');
        $response->assertSee('Warehouse Staff');
        $response->assertSee('Ayşe Yılmaz');
        $response->assertSee('Deniz Aksoy');
        $response->assertSee('Add user');
        $response->assertDontSee('Add role');
    }

    public function test_typed_role_name_is_saved_with_the_user(): void
    {
        $this->post(route('admin.users.store'), [
            'first_name' => 'Lara',
            'last_name' => 'Koç',
            'email' => 'lara.koc@nova.store',
            'phone' => '0532 000 00 02',
            'role' => 'Night Shift',
            'status' => 'active',
            'password' => 'secret123',
            'abilities' => ['pos', 'sales'],
        ])->assertRedirect(route('admin.roles.index'));

        $this->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Night Shift')
            ->assertSee('Lara Koç');

        $this->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Night Shift');

        $edit = $this->get(route('admin.users.edit', 'lara-koc'));

        $edit->assertOk();
        $edit->assertSee('value="Night Shift"', false);
    }

    public function test_custom_role_name_is_escaped_on_the_index(): void
    {
        $this->post(route('admin.users.store'), [
            'first_name' => 'Lara',
            'last_name' => 'Koç',
            'email' => 'lara.koc@nova.store',
            'role' => "<script>alert('xss')</script>",
            'status' => 'active',
            'password' => 'secret123',
            'abilities' => ['pos'],
        ]);

        $response = $this->get(route('admin.roles.index'));

        $response->assertSee("<script>alert('xss')</script>");
        $response->assertDontSee("<script>alert('xss')</script>", false);
    }

    public function test_role_detail_renders_permission_matrix(): void
    {
        $response = $this->get(route('admin.roles.show', StaffRole::StoreManager->value));

        $response->assertOk();
        $response->assertSee('Store Manager');
        $response->assertSee('Permission matrix');
        $response->assertSee('View');
        $response->assertSee('Create');
        $response->assertSee('Edit');
        $response->assertSee('Delete');
        $response->assertSee('Products');
        $response->assertSee('Inventory');
        $response->assertSee('Customers');
        $response->assertSee('Suppliers');
        $response->assertSee('Sales');
        $response->assertSee('Cash');
        $response->assertSee('Returns');
        $response->assertSee('Reports');
        $response->assertSee('Users');
        $response->assertSee('Audit');
        $response->assertSee('Settings');
        $response->assertSee('Deniz Aksoy');
        $response->assertSee('deniz.aksoy@nova.store');
    }

    public function test_cashier_matrix_allows_sales_view_and_denies_products(): void
    {
        $this->assertTrue(StaffRole::Cashier->allows('sales', 'view'));
        $this->assertFalse(StaffRole::Cashier->allows('sales', 'create'));
        $this->assertFalse(StaffRole::Cashier->allows('products', 'view'));
        $this->assertTrue(StaffRole::Cashier->allows('customers', 'edit'));
        $this->assertTrue(StaffRole::WarehouseStaff->allows('inventory', 'edit'));
        $this->assertFalse(StaffRole::WarehouseStaff->allows('cash', 'view'));
        $this->assertTrue(StaffRole::SuperAdmin->allows('users', 'delete'));
    }

    public function test_unknown_role_returns_404(): void
    {
        $this->get(route('admin.roles.show', 'guest'))->assertNotFound();
    }

    public function test_cashier_is_forbidden_from_roles(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }
}
