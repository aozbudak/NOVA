<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_customer_account_that_can_log_in(): void
    {
        $this->post(route('admin.customers.store'), [
            'name' => 'Ahmet Yılmaz',
            'email' => 'ahmet@example.com',
            'phone' => '05321234567',
            'password' => 'password123',
        ])->assertRedirect(route('admin.customers.index'));

        $user = User::query()->where('email', 'ahmet@example.com')->first();
        $customer = Customer::query()->where('email', 'ahmet@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($customer);
        $this->assertSame($user->id, $customer->user_id);
        $this->assertTrue(Hash::check('password123', $user->password));

        $this->post(route('login.store'), [
            'email' => 'ahmet@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('account.show'));
    }

    public function test_customer_create_rejects_a_short_password(): void
    {
        $this->from(route('admin.customers.index'))
            ->post(route('admin.customers.store'), [
                'name' => 'Ahmet Yılmaz',
                'email' => 'ahmet@example.com',
                'password' => 'short',
            ])
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHasErrors('password');

        $this->assertFalse(User::query()->where('email', 'ahmet@example.com')->exists());
    }

    public function test_customers_form_includes_a_password_field(): void
    {
        $this->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee('name="password"', false);
    }
}
