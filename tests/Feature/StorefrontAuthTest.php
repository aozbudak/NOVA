<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontAuthTest extends TestCase
{
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
}
