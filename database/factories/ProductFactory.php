<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'brand_id' => null,
            'name' => Str::headline($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->sentence(),
            'brand' => 'NOVA',
            'base_price' => fake()->randomFloat(2, 49, 499),
            'vat_rate' => 20,
            'currency' => 'EUR',
            'is_new' => false,
            'is_featured' => false,
            'is_active' => true,
        ];
    }
}
