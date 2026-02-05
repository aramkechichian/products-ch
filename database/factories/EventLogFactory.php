<?php

namespace Database\Factories;

use App\Models\EventLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventLog>
 */
class EventLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'event_type' => fake()->randomElement(['POST', 'PUT', 'DELETE']),
            'resource_type' => fake()->randomElement(['Product', 'Currency', 'ProductPrice']),
            'resource_id' => fake()->numberBetween(1, 100),
            'endpoint' => fake()->randomElement(['/api/v1/products', '/api/v1/currencies', '/api/v1/products/1/prices']),
            'method' => fake()->randomElement(['POST', 'PUT', 'DELETE']),
            'data' => [
                'payload' => [
                    'name' => fake()->word(),
                    'price' => fake()->randomFloat(2, 10, 1000),
                ],
            ],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
