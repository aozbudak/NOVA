<?php

namespace Database\Factories;

use App\Enums\StorefrontCoverSlot;
use App\Models\StorefrontCover;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorefrontCover>
 */
class StorefrontCoverFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slot' => fake()->unique()->randomElement(StorefrontCoverSlot::cases()),
            'image_url' => 'https://example.com/covers/'.fake()->unique()->slug().'.jpg',
        ];
    }
}
