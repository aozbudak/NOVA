<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome back');
    }

    public function test_register_page_renders(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create account');
    }

    public function test_login_rejects_an_invalid_email(): void
    {
        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'not-an-email',
                'password' => 'password123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_register_stores_the_customer_and_opens_account(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('account.show'));

        $this->get(route('account.show'))
            ->assertOk()
            ->assertSee('Ada');
    }

    public function test_registered_customer_can_log_in_again(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->post(route('account.logout'));

        $this->post(route('login.store'), [
            'email' => 'ada@nova.example',
            'password' => 'password123',
        ])->assertRedirect(route('account.show'));

        $this->get(route('account.show'))
            ->assertOk()
            ->assertSee('Ada');
    }

    public function test_inactive_customer_cannot_log_in(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        User::query()->where('email', 'ada@nova.example')->update(['is_active' => false]);

        $this->post(route('account.logout'));

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'ada@nova.example',
                'password' => 'password123',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => __('auth.inactive')]);
    }

    public function test_register_rejects_a_duplicate_email(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@nova.example',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('account.show'));

        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Ada',
                'last_name' => 'Byron',
                'email' => 'ada@nova.example',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
    }
}
