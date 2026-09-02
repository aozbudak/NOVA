<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleSwitchTest extends TestCase
{
    public function test_storefront_defaults_to_english(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('The new standard')
            ->assertSee('Women')
            ->assertSee('html lang="en"', false);
    }

    public function test_switching_to_turkish_translates_the_storefront(): void
    {
        $this->from(route('home'))
            ->get(route('locale.update', 'tr'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'tr')
            ->assertCookie('locale', 'tr');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Kadın')
            ->assertSee('Yeni gelenler')
            ->assertSee('html lang="tr"', false);
    }

    public function test_switching_to_german_translates_navigation(): void
    {
        $this->withSession(['locale' => 'de'])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Damen')
            ->assertSee('Neuheiten');
    }

    public function test_unknown_locale_returns_404(): void
    {
        $this->get('/locale/xx')->assertNotFound();
    }

    public function test_admin_panel_follows_the_selected_locale(): void
    {
        $this->withSession(['locale' => 'tr'])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('html lang="tr"', false)
            ->assertSee('Ürünler')
            ->assertSee('Mağaza')
            ->assertSee('Çıkış');
    }

    public function test_admin_panel_follows_german_and_french_locales(): void
    {
        $this->withSession(['locale' => 'de'])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('html lang="de"', false)
            ->assertSee('Produkte')
            ->assertSee('Abmelden');

        $this->withSession(['locale' => 'fr'])
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('html lang="fr"', false)
            ->assertSee('Produits')
            ->assertSee('Se déconnecter');
    }
}
