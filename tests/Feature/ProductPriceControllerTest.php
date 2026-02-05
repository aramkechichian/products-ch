<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductPriceControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private User $user;
    private string $token;
    private Currency $baseCurrency;
    private Currency $targetCurrency;
    private Product $product;

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
        
        // Create currencies
        $this->baseCurrency = Currency::factory()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'exchange_rate' => 1.0000,
        ]);

        $this->targetCurrency = Currency::factory()->create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.85,
        ]);

        // Create a product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'currency_id' => $this->baseCurrency->id,
        ]);
    }

    /**
     * Test that unauthenticated requests return 401.
     */
    public function test_unauthenticated_requests_return_401(): void
    {
        $response = $this->getJson("/api/v1/products/{$this->product->id}/prices");
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test listing product prices for a product.
     */
    public function test_can_list_product_prices(): void
    {
        // Create some product prices
        ProductPrice::factory()->create([
            'product_id' => $this->product->id,
            'currency_id' => $this->targetCurrency->id,
            'price' => 85.00,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/products/{$this->product->id}/prices");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'product_id',
                        'currency_id',
                        'currency',
                        'price',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product prices retrieved successfully',
            ]);

        $prices = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($prices));
    }

    /**
     * Test listing product prices for a non-existent product returns 404.
     */
    public function test_listing_prices_for_non_existent_product_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/999/prices');

        $response->assertStatus(404);
    }

    /**
     * Test creating a product price.
     */
    public function test_can_create_product_price(): void
    {
        $priceData = [
            'currency_id' => $this->targetCurrency->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/products/{$this->product->id}/prices", $priceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'product_id',
                    'currency_id',
                    'currency',
                    'price',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product price created successfully',
                'data' => [
                    'product_id' => $this->product->id,
                    'currency_id' => $this->targetCurrency->id,
                ],
            ]);

        // Verify the price was calculated correctly: product.price * currency.exchange_rate
        $expectedPrice = $this->product->price * $this->targetCurrency->exchange_rate;
        $this->assertEquals($expectedPrice, $response->json('data.price'));

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $this->product->id,
            'currency_id' => $this->targetCurrency->id,
            'price' => round($expectedPrice, 2),
        ]);
    }

    /**
     * Test updating an existing product price.
     */
    public function test_can_update_existing_product_price(): void
    {
        // Create an existing product price
        $existingPrice = ProductPrice::create([
            'product_id' => $this->product->id,
            'currency_id' => $this->targetCurrency->id,
            'price' => 50.00, // Old price
        ]);

        // Update the product price (should recalculate)
        $priceData = [
            'currency_id' => $this->targetCurrency->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/products/{$this->product->id}/prices", $priceData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product price updated successfully',
            ]);

        // Verify the price was updated with the correct calculation
        $expectedPrice = $this->product->price * $this->targetCurrency->exchange_rate;
        $this->assertEquals($expectedPrice, $response->json('data.price'));

        // Verify only one price exists (updated, not duplicated)
        $priceCount = ProductPrice::where('product_id', $this->product->id)
            ->where('currency_id', $this->targetCurrency->id)
            ->count();
        $this->assertEquals(1, $priceCount);
    }

    /**
     * Test cannot create product price with same currency as product base currency.
     */
    public function test_cannot_create_product_price_with_same_currency_as_base(): void
    {
        $priceData = [
            'currency_id' => $this->baseCurrency->id, // Same as product's base currency
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/products/{$this->product->id}/prices", $priceData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['currency_id']);
    }

    /**
     * Test cannot create product price with non-existent currency.
     */
    public function test_cannot_create_product_price_with_non_existent_currency(): void
    {
        $priceData = [
            'currency_id' => 99999,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/products/{$this->product->id}/prices", $priceData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['currency_id']);
    }

    /**
     * Test cannot create product price for non-existent product.
     */
    public function test_cannot_create_product_price_for_non_existent_product(): void
    {
        $priceData = [
            'currency_id' => $this->targetCurrency->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/products/999/prices', $priceData);

        $response->assertStatus(404);
    }

    /**
     * Test exporting product prices to Excel.
     */
    public function test_can_export_product_prices_to_excel(): void
    {
        // Create a new product and currency to avoid conflicts
        $newCurrency = Currency::factory()->create([
            'name' => 'Export Test Currency ' . uniqid(),
            'symbol' => 'EXP' . uniqid(),
            'exchange_rate' => 1.0,
        ]);
        
        $newProduct = Product::factory()->create([
            'currency_id' => $newCurrency->id,
        ]);

        // Create some product prices with unique currency to avoid conflicts
        $currency1 = Currency::factory()->create([
            'name' => 'Currency 1 ' . uniqid(),
            'symbol' => 'C1' . uniqid(),
            'exchange_rate' => 0.85,
        ]);
        
        $currency2 = Currency::factory()->create([
            'name' => 'Currency 2 ' . uniqid(),
            'symbol' => 'C2' . uniqid(),
            'exchange_rate' => 0.75,
        ]);

        ProductPrice::create([
            'product_id' => $newProduct->id,
            'currency_id' => $currency1->id,
            'price' => 85.00,
        ]);

        ProductPrice::create([
            'product_id' => $newProduct->id,
            'currency_id' => $currency2->id,
            'price' => 75.00,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/v1/product-prices/export');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertStringContainsString('product_prices_', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));
    }

    /**
     * Test exporting product prices requires authentication.
     */
    public function test_export_product_prices_requires_authentication(): void
    {
        $response = $this->get('/api/v1/product-prices/export');

        $response->assertStatus(401);
    }

    /**
     * Test product price calculation with different exchange rates.
     */
    public function test_product_price_calculation_with_different_exchange_rates(): void
    {
        // Create currency with different exchange rate
        $gbpCurrency = Currency::factory()->create([
            'name' => 'British Pound',
            'symbol' => 'GBP',
            'exchange_rate' => 0.75,
        ]);

        $priceData = [
            'currency_id' => $gbpCurrency->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/v1/products/{$this->product->id}/prices", $priceData);

        $response->assertStatus(201);

        // Verify calculation: 100.00 * 0.75 = 75.00
        $expectedPrice = $this->product->price * $gbpCurrency->exchange_rate;
        $this->assertEquals($expectedPrice, $response->json('data.price'));
        $this->assertEquals(75.00, $response->json('data.price'));
    }
}
