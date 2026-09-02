<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'title' => 'Home',
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->numerify('05## ### ## ##'),
            'city' => fake()->city(),
            'district' => fake()->city(),
            'address_line' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'is_default' => false,
        ];
    }
}
