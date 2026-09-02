<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    public function test_users_table_renders_status_and_add_action(): void
    {
        $response = $this->get(route('admin.roles.index'));

        $response->assertOk();
        $response->assertSee('Roles & permissions');
        $response->assertSee('Add user');
        $response->assertSee('Super Admin');
        $response->assertSee('Store Manager');
        $response->assertSee('Cashier');
        $response->assertSee('Warehouse Staff');
        $response->assertSee('Ayşe Yılmaz');
        $response->assertSee('ayse.yilmaz@nova.store');
        $response->assertSee('Active');
        $response->assertSee('Inactive');
        $response->assertSee('Last login');
        $response->assertSee('Created at');
        $response->assertSeeInOrder([
            'Super Admin',
            'NOVA Admin',
            'Store Manager',
            'Deniz Aksoy',
            'Cashier',
            'Ayşe Yılmaz',
            'Mert Kaya',
            'Warehouse Staff',
            'Ece Yılmaz',
        ]);
    }

    public function test_users_index_lists_staff_and_add_action(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Users');
        $response->assertSee('Add user');
        $response->assertSee('Ayşe Yılmaz');
        $response->assertSee('ayse.yilmaz@nova.store');
        $response->assertSee('Cashier');
        $response->assertSee('Active');
    }

    public function test_create_form_collects_identity_role_status_and_password(): void
    {
        $response = $this->get(route('admin.users.create'));

        $response->assertOk();
        $response->assertSee('First name');
        $response->assertSee('Last name');
        $response->assertSee('Email');
        $response->assertSee('Username');
        $response->assertSee('Phone');
        $response->assertSee('Role');
        $response->assertSee('Pick a prepared role or type a new name.');
        $response->assertSee('list="staff-roles"', false);
        $response->assertSee('Status');
        $response->assertSee('Password');
        $response->assertSee('type="password"', false);
        $response->assertSee('Minimum 8 characters');
        $response->assertSee('Super Admin');
        $response->assertSee('Operations');
        $response->assertSee('Products');
        $response->assertSee('Categories');
        $response->assertSee('name="abilities[]"', false);
    }

    public function test_edit_form_does_not_show_the_stored_password(): void
    {
        $response = $this->get(route('admin.users.edit', 'ayse-yilmaz'));

        $response->assertOk();
        $response->assertSee('Ayşe');
        $response->assertSee('Yılmaz');
        $response->assertSee('ayse.yilmaz@nova.store');
        $response->assertSee('value="ayse"', false);
        $response->assertSee('type="password"', false);
        $response->assertSee('Leave blank to keep the current password.');
        $response->assertSee('value="Cashier"', false);
        $response->assertSee('Operations');
        $response->assertDontSee('hashed');
        $response->assertDontSee('secret');
    }

    public function test_empty_create_payload_is_rejected(): void
    {
        $this->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'username', 'role', 'status', 'password']);
    }

    public function test_valid_create_payload_persists_user_and_assigned_operations(): void
    {
        $this->post(route('admin.users.store'), [
            'first_name' => 'Lara',
            'last_name' => 'Koç',
            'email' => 'lara.koc@nova.store',
            'username' => 'larakoc',
            'phone' => '0532 000 00 02',
            'role' => StaffRole::Cashier->value,
            'status' => 'active',
            'password' => 'secret123',
            'abilities' => ['pos', 'sales', 'customers', 'returns', 'products', 'categories'],
        ])->assertRedirect(route('admin.users.index'));

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Lara Koç')
            ->assertSee('lara.koc@nova.store');

        $edit = $this->get(route('admin.users.edit', 'lara-koc'));

        $edit->assertOk();
        $edit->assertSee('Lara');
        $this->assertMatchesRegularExpression('/value="products"[^>]*\bchecked\b/', $edit->getContent());
        $this->assertMatchesRegularExpression('/value="categories"[^>]*\bchecked\b/', $edit->getContent());
    }

    public function test_edit_persists_extra_operations_on_an_existing_user(): void
    {
        $this->put(route('admin.users.update', 'ayse-yilmaz'), [
            'first_name' => 'Ayşe',
            'last_name' => 'Yılmaz',
            'email' => 'ayse.yilmaz@nova.store',
            'username' => 'ayse',
            'phone' => '0532 441 00 11',
            'role' => StaffRole::Cashier->value,
            'status' => 'active',
            'abilities' => ['pos', 'sales', 'customers', 'returns', 'products'],
        ])->assertRedirect(route('admin.users.index'));

        $edit = $this->get(route('admin.users.edit', 'ayse-yilmaz'));

        $edit->assertOk();
        $this->assertMatchesRegularExpression('/value="products"[^>]*\bchecked\b/', $edit->getContent());
    }

    public function test_unknown_operation_is_rejected(): void
    {
        $this->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'first_name' => 'Lara',
                'last_name' => 'Koç',
                'email' => 'lara.koc@nova.store',
                'username' => 'larakoc',
                'role' => StaffRole::Cashier->value,
                'status' => 'active',
                'password' => 'secret123',
                'abilities' => ['not-a-module'],
            ])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors('abilities.0');
    }

    public function test_edit_without_password_keeps_the_account(): void
    {
        $this->put(route('admin.users.update', 'ayse-yilmaz'), [
            'first_name' => 'Ayşe',
            'last_name' => 'Yılmaz',
            'email' => 'ayse.yilmaz@nova.store',
            'username' => 'ayse',
            'phone' => '0532 441 00 11',
            'role' => StaffRole::Cashier->value,
            'status' => 'active',
        ])->assertRedirect(route('admin.users.index'));
    }

    public function test_unknown_user_returns_404(): void
    {
        $this->get(route('admin.users.edit', 'missing'))->assertNotFound();
    }

    public function test_cashier_is_forbidden_from_users(): void
    {
        $this->withSession(['admin.role' => StaffRole::Cashier->value])
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }
}
