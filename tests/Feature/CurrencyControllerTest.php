<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and generate token for authenticated requests
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /**
     * Test that unauthenticated requests return 401.
     */
    public function test_unauthenticated_requests_return_401(): void
    {
        $response = $this->getJson('/api/v1/currencies');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test listing all currencies.
     */
    public function test_can_list_currencies(): void
    {
        $initialCount = Currency::count();
        Currency::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/currencies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'symbol',
                        'exchange_rate',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Currencies retrieved successfully',
            ]);

        // Verificar que hay al menos las 3 currencies creadas (puede haber más de otros tests)
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
        $this->assertEquals($initialCount + 3, count($response->json('data')));
    }

    /**
     * Test creating a currency with valid data.
     */
    public function test_can_create_currency_with_valid_data(): void
    {
        // Usar datos únicos para evitar conflictos con datos existentes
        $uniqueName = 'Test Currency ' . uniqid();
        $uniqueSymbol = 'TC' . substr(uniqid(), -3);
        
        $currencyData = [
            'name' => $uniqueName,
            'symbol' => $uniqueSymbol,
            'exchange_rate' => 1.0000,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/currencies', $currencyData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'symbol',
                    'exchange_rate',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Currency created successfully',
                'data' => [
                    'name' => $uniqueName,
                    'symbol' => $uniqueSymbol,
                    'exchange_rate' => 1.0000,
                ],
            ]);

        $this->assertDatabaseHas('currencies', [
            'name' => $uniqueName,
            'symbol' => $uniqueSymbol,
            'exchange_rate' => 1.0000,
        ]);
    }

    /**
     * Test creating a currency with invalid data.
     */
    public function test_cannot_create_currency_with_invalid_data(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/currencies', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test creating a currency with duplicate name.
     */
    public function test_cannot_create_currency_with_duplicate_name(): void
    {
        Currency::factory()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
        ]);

        $currencyData = [
            'name' => 'US Dollar',
            'symbol' => 'EUR',
            'exchange_rate' => 1.0000,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/currencies', $currencyData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test creating a currency with duplicate symbol.
     */
    public function test_cannot_create_currency_with_duplicate_symbol(): void
    {
        Currency::factory()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
        ]);

        $currencyData = [
            'name' => 'Euro',
            'symbol' => 'USD',
            'exchange_rate' => 1.0000,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/currencies', $currencyData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test showing a specific currency.
     */
    public function test_can_show_currency(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'exchange_rate' => 1.0000,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/currencies/{$currency->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'symbol',
                    'exchange_rate',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Currency retrieved successfully',
                'data' => [
                    'id' => $currency->id,
                    'name' => 'US Dollar',
                    'symbol' => 'USD',
                    'exchange_rate' => 1.0000,
                ],
            ]);
    }

    /**
     * Test showing a non-existent currency returns 404.
     */
    public function test_showing_non_existent_currency_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/currencies/999');

        $response->assertStatus(404);
    }

    /**
     * Test updating a currency with valid data.
     */
    public function test_can_update_currency_with_valid_data(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'exchange_rate' => 1.0000,
        ]);

        $updateData = [
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.8500,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/currencies/{$currency->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Currency updated successfully',
                'data' => [
                    'id' => $currency->id,
                    'name' => 'Euro',
                    'symbol' => 'EUR',
                    'exchange_rate' => 0.8500,
                ],
            ]);

        $this->assertDatabaseHas('currencies', [
            'id' => $currency->id,
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.8500,
        ]);
    }

    /**
     * Test updating a currency with partial data.
     */
    public function test_can_update_currency_with_partial_data(): void
    {
        $currency = Currency::factory()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'exchange_rate' => 1.0000,
        ]);

        $updateData = [
            'name' => 'Euro',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/currencies/{$currency->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $currency->id,
                    'name' => 'Euro',
                ],
            ]);
    }

    /**
     * Test updating a currency with duplicate name.
     */
    public function test_cannot_update_currency_with_duplicate_name(): void
    {
        Currency::factory()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
        ]);

        $currency = Currency::factory()->create([
            'name' => 'Euro',
            'symbol' => 'EUR',
        ]);

        $updateData = [
            'name' => 'US Dollar',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/currencies/{$currency->id}", $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Test deleting a currency.
     */
    public function test_can_delete_currency(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/currencies/{$currency->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Currency deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('currencies', [
            'id' => $currency->id,
        ]);
    }

    /**
     * Test deleting a currency with associated products returns 409.
     */
    public function test_cannot_delete_currency_with_associated_products(): void
    {
        $currency = Currency::factory()->create();
        
        // Create a product associated with this currency
        \App\Models\Product::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/currencies/{$currency->id}");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete currency because it has associated products',
            ]);

        $this->assertDatabaseHas('currencies', [
            'id' => $currency->id,
        ]);
    }

    /**
     * Test deleting a non-existent currency returns 404.
     */
    public function test_deleting_non_existent_currency_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/currencies/999');

        $response->assertStatus(404);
    }
}
