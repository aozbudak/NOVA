<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 2500);

        return [
            'order_number' => 'NOVA-'.fake()->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'total_amount' => $subtotal,
            'currency' => 'TRY',
        ];
    }
}
