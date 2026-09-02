<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => Str::upper(fake()->unique()->bothify('NOVA-###??')),
            'barcode' => fake()->unique()->numerify('8680001######'),
            'color' => fake()->randomElement(['Black', 'White', 'Navy']),
            'size' => fake()->randomElement(['S', 'M', 'L']),
            'price' => fake()->randomFloat(2, 49, 499),
            'is_active' => true,
        ];
    }
}
