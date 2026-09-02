<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiConnectionTest extends TestCase
{
    public function test_storefront_cart_wishlist_and_catalog_use_the_api(): void
    {
        $this->getJson(route('api.catalog.index'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Structured Wool Coat');

        $this->getJson(route('api.catalog.show', 'structured-wool-coat'))
            ->assertOk()
            ->assertJsonPath('data.slug', 'structured-wool-coat');

        $this->getJson(route('api.search', ['q' => 'blazer']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Tailored Crepe Blazer']);

        $this->postJson(route('api.cart.store'), [
            'product_id' => 1,
            'size' => 'M',
            'quantity' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('count', 1);

        $this->getJson(route('api.cart.index'))
            ->assertOk()
            ->assertJsonPath('count', 1);

        $this->postJson(route('api.wishlist.store'), ['product_id' => 2])
            ->assertOk()
            ->assertJsonPath('added', true);
    }

    public function test_admin_product_sale_return_cash_and_inventory_apis_exist(): void
    {
        $this->getJson(route('api.products.index'))
            ->assertOk()
            ->assertJsonFragment(['sku' => 'NOVA01']);

        $this->getJson(route('api.products.show', 'basic-shirt'))
            ->assertOk()
            ->assertJsonPath('data.name', 'Basic Shirt');

        $this->putJson(route('api.products.update', 'basic-shirt'))
            ->assertOk()
            ->assertJsonPath('status', 'updated');

        $this->postJson(route('api.sales.store'), [
            'payment' => 'cash',
            'items' => [
                ['sku' => 'NOVA01-WHI-S', 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.payment', 'cash');

        $this->getJson(route('api.sales.show', 'NOVA-1024'))
            ->assertOk()
            ->assertJsonPath('data.number', 'NOVA-1024');

        $this->postJson(route('api.returns.store'), ['sale' => 'NOVA-1024'])
            ->assertCreated();

        $this->postJson(route('api.cash.close'), ['actual' => 14060])
            ->assertOk()
            ->assertJsonPath('status', 'closed');

        $this->postJson(route('api.inventory.adjust'), [
            'sku' => 'NOVA01-WHI-S',
            'quantity' => -1,
        ])
            ->assertOk()
            ->assertJsonPath('status', 'updated');
    }

    public function test_admin_search_customers_and_pos_items_are_available(): void
    {
        $this->getJson(route('api.admin.search', ['q' => 'Elif']))
            ->assertOk()
            ->assertJsonPath('groups.customers.0.label', 'Elif Kaya');

        $this->getJson(route('api.customers.show', 'elif-kaya'))
            ->assertOk()
            ->assertJsonPath('data.name', 'Elif Kaya');

        $this->getJson(route('api.pos.items'))
            ->assertOk()
            ->assertJsonFragment(['sku' => 'NOVA01-WHI-S']);

        $this->getJson(route('api.suppliers.show', 'atelier-mills'))
            ->assertOk()
            ->assertJsonPath('data.name', 'Atelier Mills');
    }

    public function test_unknown_api_product_returns_404(): void
    {
        $this->getJson(route('api.products.show', 'missing'))->assertNotFound();
        $this->putJson(route('api.products.update', 'missing'))->assertNotFound();
        $this->postJson(route('api.inventory.adjust'), [
            'sku' => 'MISSING',
            'quantity' => 1,
        ])->assertNotFound();
    }
}
