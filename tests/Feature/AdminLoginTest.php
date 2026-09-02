<?php

namespace Tests\Feature;

use App\Enums\StaffRole;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    protected bool $authenticateAdmin = false;

    public function test_login_page_asks_for_username_and_password(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Staff login')
            ->assertSee('Username')
            ->assertSee('Password')
            ->assertDontSee('Welcome back')
            ->assertDontSee('Create account');
    }

    public function test_guest_is_sent_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'username' => 'ayse',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('username');
    }

    public function test_inactive_staff_cannot_sign_in(): void
    {
        $this->withSession(['admin.authenticated' => true])
            ->post(route('admin.users.store'), [
                'first_name' => 'Pasif',
                'last_name' => 'Kasa',
                'email' => 'pasif.kasa@nova.store',
                'username' => 'pasifkasa',
                'role' => StaffRole::Cashier->value,
                'status' => 'inactive',
                'password' => 'secret123',
                'abilities' => ['pos'],
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->post(route('admin.logout'));

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'username' => 'pasifkasa',
                'password' => 'secret123',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['username' => __('auth.inactive')]);

        $this->assertNotSame(true, session('admin.authenticated'));
    }

    public function test_active_staff_signs_in_with_username(): void
    {
        $this->withSession(['admin.authenticated' => true])
            ->post(route('admin.users.store'), [
                'first_name' => 'Lara',
                'last_name' => 'Koç',
                'email' => 'lara.koc@nova.store',
                'username' => 'larakoc',
                'role' => StaffRole::Cashier->value,
                'status' => 'active',
                'password' => 'secret123',
                'abilities' => ['pos', 'sales', 'customers', 'returns'],
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.login.store'), [
            'username' => 'larakoc',
            'password' => 'secret123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertTrue(session('admin.authenticated'));
        $this->assertSame(StaffRole::Cashier->value, session('admin.role'));

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.pos.index'));
    }
}
