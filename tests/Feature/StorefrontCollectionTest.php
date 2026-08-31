<?php

namespace Tests\Feature;

use Tests\TestCase;

class StorefrontCollectionTest extends TestCase
{
    public function test_women_listing_renders_products_and_count(): void
    {
        $response = $this->get(route('shop.show', 'women'));

        $response->assertOk();
        $response->assertSee('Women');
        $response->assertSee('Structured Wool Coat');
        $response->assertSee('products');
    }

    public function test_sale_listing_only_includes_reduced_prices(): void
    {
        $response = $this->get(route('shop.show', 'sale'));

        $response->assertOk();
        $response->assertViewHas('products', function ($products): bool {
            return $products->isNotEmpty()
                && $products->every(fn (array $product): bool => $product['oldPrice'] !== null)
                && $products->contains(fn (array $product): bool => $product['name'] === 'Tailored Crepe Blazer');
        });
    }

    public function test_unknown_department_returns_404(): void
    {
        $this->get('/shop/atelier')->assertNotFound();
    }

    public function test_kids_listing_renders_childrenswear(): void
    {
        $this->get(route('shop.show', 'kids'))
            ->assertOk()
            ->assertSee('Kids')
            ->assertSee('Cotton Jersey Dress');
    }

    public function test_sport_listing_renders_performance_pieces(): void
    {
        $this->get(route('shop.show', 'sport'))
            ->assertOk()
            ->assertSee('Sport')
            ->assertSee('Performance Knit');
    }

    public function test_invalid_sort_falls_back_to_recommended_order(): void
    {
        $response = $this->get(route('shop.show', ['department' => 'women', 'sort' => 'price;drop table']));

        $response->assertOk();
        $response->assertSee('Structured Wool Coat');
    }

    public function test_size_filter_excludes_out_of_stock_sizes(): void
    {
        $response = $this->get(route('shop.show', ['department' => 'women', 'size' => 'M']));

        $response->assertOk();
        $response->assertViewHas('products', function ($products): bool {
            return $products->every(fn (array $product): bool => $product['name'] !== 'Wide-Leg Wool Trousers');
        });
    }
}
