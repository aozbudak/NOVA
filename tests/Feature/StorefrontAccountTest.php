<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontAccountTest extends TestCase
{
    public function test_account_redirects_guests_to_login(): void
    {
        $this->get(route('account.show'))->assertRedirect(route('login'));
    }

    public function test_static_page_renders_editorial_copy(): void
    {
        $this->get(route('pages.show', 'about'))
            ->assertOk()
            ->assertSee('About NOVA')
            ->assertSee('contemporary fashion house');
    }

    public function test_unknown_static_page_returns_404(): void
    {
        $this->get(route('pages.show', 'secret'))->assertNotFound();
    }
}
