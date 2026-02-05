<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private User $user;
    private string $token;
    private Currency $currency;

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
        
        // Create a default currency for products
        $this->currency = Currency::factory()->create([
            'name' => 'US Dollar',
            'symbol' => 'USD',
            'exchange_rate' => 1.0000,
        ]);
    }

    /**
     * Test that unauthenticated requests return 401.
     */
    public function test_unauthenticated_requests_return_401(): void
    {
        $response = $this->getJson('/api/v1/products');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test listing all products.
     */
    public function test_can_list_products(): void
    {
        $initialCount = Product::count();
        Product::factory()->count(3)->create([
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'currency',
                        'currency_id',
                        'tax_cost',
                        'manufacturing_cost',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Products retrieved successfully',
            ]);

        // Verify that there are at least the 3 products created
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
        $this->assertEquals($initialCount + 3, count($response->json('data')));
    }

    /**
     * Test creating a product with valid data.
     */
    public function test_can_create_product_with_valid_data(): void
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'currency_id' => $this->currency->id,
            'tax_cost' => 10.00,
            'manufacturing_cost' => 50.00,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'currency',
                    'currency_id',
                    'tax_cost',
                    'manufacturing_cost',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => [
                    'name' => 'Test Product',
                    'description' => 'This is a test product',
                    'price' => 99.99,
                    'currency_id' => $this->currency->id,
                    'tax_cost' => 10.00,
                    'manufacturing_cost' => 50.00,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'currency_id' => $this->currency->id,
            'tax_cost' => 10.00,
            'manufacturing_cost' => 50.00,
        ]);
    }

    /**
     * Test creating a product with minimal required data.
     */
    public function test_can_create_product_with_minimal_data(): void
    {
        $productData = [
            'name' => 'Minimal Product',
            'description' => 'Minimal description',
            'price' => 50.00,
            'currency_id' => $this->currency->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Minimal Product',
            'description' => 'Minimal description',
            'price' => 50.00,
            'currency_id' => $this->currency->id,
            'tax_cost' => 0.00,
            'manufacturing_cost' => 0.00,
        ]);
    }

    /**
     * Test creating a product with invalid data.
     */
    public function test_cannot_create_product_with_invalid_data(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/products', []);

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
     * Test creating a product with non-existent currency_id.
     */
    public function test_cannot_create_product_with_non_existent_currency_id(): void
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'currency_id' => 99999, // Non-existent currency
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['currency_id']);
    }

    /**
     * Test creating a product with negative price.
     */
    public function test_cannot_create_product_with_negative_price(): void
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => -10.00,
            'currency_id' => $this->currency->id,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/products', $productData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['price']);
    }

    /**
     * Test showing a specific product.
     */
    public function test_can_show_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 99.99,
            'currency_id' => $this->currency->id,
            'tax_cost' => 10.00,
            'manufacturing_cost' => 50.00,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'currency',
                    'currency_id',
                    'tax_cost',
                    'manufacturing_cost',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                    'description' => 'Test description',
                    'price' => 99.99,
                    'currency_id' => $this->currency->id,
                ],
            ]);
    }

    /**
     * Test showing a non-existent product returns 404.
     */
    public function test_showing_non_existent_product_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/999');

        $response->assertStatus(404);
    }

    /**
     * Test updating a product with valid data.
     */
    public function test_can_update_product_with_valid_data(): void
    {
        $product = Product::factory()->create([
            'currency_id' => $this->currency->id,
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 199.99,
            'tax_cost' => 20.00,
            'manufacturing_cost' => 100.00,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'id' => $product->id,
                    'name' => 'Updated Product',
                    'description' => 'Updated description',
                    'price' => 199.99,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 199.99,
            'tax_cost' => 20.00,
            'manufacturing_cost' => 100.00,
        ]);
    }

    /**
     * Test updating a product with partial data.
     */
    public function test_can_update_product_with_partial_data(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Name',
            'currency_id' => $this->currency->id,
        ]);

        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => 'Updated Name',
                ],
            ]);
    }

    /**
     * Test updating a product with non-existent currency_id.
     */
    public function test_cannot_update_product_with_non_existent_currency_id(): void
    {
        $product = Product::factory()->create([
            'currency_id' => $this->currency->id,
        ]);

        $updateData = [
            'currency_id' => 99999, // Non-existent currency
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['currency_id']);
    }

    /**
     * Test deleting a product.
     */
    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create([
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test deleting a non-existent product returns 404.
     */
    public function test_deleting_non_existent_product_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/products/999');

        $response->assertStatus(404);
    }

    /**
     * Test searching products by name.
     */
    public function test_can_search_products_by_name(): void
    {
        Product::factory()->create([
            'name' => 'Laptop Pro',
            'currency_id' => $this->currency->id,
        ]);
        Product::factory()->create([
            'name' => 'Desktop Computer',
            'currency_id' => $this->currency->id,
        ]);
        Product::factory()->create([
            'name' => 'Gaming Laptop',
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?name=Laptop');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Products found successfully',
            ]);

        $products = $response->json('data.data');
        $this->assertGreaterThanOrEqual(2, count($products));
        foreach ($products as $product) {
            $this->assertStringContainsStringIgnoringCase('Laptop', $product['name']);
        }
    }

    /**
     * Test searching products by currency symbol.
     */
    public function test_can_search_products_by_currency_symbol(): void
    {
        $eurCurrency = Currency::factory()->create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.85,
        ]);

        Product::factory()->create([
            'currency_id' => $this->currency->id, // USD
        ]);
        Product::factory()->create([
            'currency_id' => $eurCurrency->id, // EUR
        ]);
        Product::factory()->create([
            'currency_id' => $this->currency->id, // USD
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?currency_symbol=EUR');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $products = $response->json('data.data');
        $this->assertCount(1, $products);
        $this->assertEquals('EUR', $products[0]['currency']['symbol']);
    }

    /**
     * Test searching products by price range.
     */
    public function test_can_search_products_by_price_range(): void
    {
        Product::factory()->create([
            'price' => 50.00,
            'currency_id' => $this->currency->id,
        ]);
        Product::factory()->create([
            'price' => 150.00,
            'currency_id' => $this->currency->id,
        ]);
        Product::factory()->create([
            'price' => 250.00,
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?min_price=100&max_price=200');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $products = $response->json('data.data');
        $this->assertCount(1, $products);
        $this->assertEquals(150.00, $products[0]['price']);
    }

    /**
     * Test searching products with sorting.
     */
    public function test_can_search_products_with_sorting(): void
    {
        // Use unique prefix to avoid conflicts with other tests
        $uniquePrefix = 'SortTest' . uniqid();
        
        $zebra = Product::factory()->create([
            'name' => $uniquePrefix . ' Zebra',
            'price' => 100.00,
            'currency_id' => $this->currency->id,
        ]);
        $apple = Product::factory()->create([
            'name' => $uniquePrefix . ' Apple',
            'price' => 200.00,
            'currency_id' => $this->currency->id,
        ]);
        $banana = Product::factory()->create([
            'name' => $uniquePrefix . ' Banana',
            'price' => 50.00,
            'currency_id' => $this->currency->id,
        ]);

        // Test sorting by name ascending
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?name=' . urlencode($uniquePrefix) . '&sort_by=name&sort_order=asc');

        $response->assertStatus(200);
        $products = $response->json('data.data');
        
        // Filter to get only our products
        $ourProducts = array_filter($products, function ($product) use ($uniquePrefix) {
            return strpos($product['name'], $uniquePrefix) === 0;
        });
        $ourProducts = array_values($ourProducts); // Re-index array
        
        $this->assertCount(3, $ourProducts, 'Should have exactly 3 products');
        
        // Verify sorting: Apple < Banana < Zebra (ascending order)
        $this->assertEquals($uniquePrefix . ' Apple', $ourProducts[0]['name']);
        $this->assertEquals($uniquePrefix . ' Banana', $ourProducts[1]['name']);
        $this->assertEquals($uniquePrefix . ' Zebra', $ourProducts[2]['name']);

        // Test sorting by price descending
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?name=' . urlencode($uniquePrefix) . '&sort_by=price&sort_order=desc');

        $response->assertStatus(200);
        $products = $response->json('data.data');
        
        // Filter to get only our products
        $ourProducts = array_filter($products, function ($product) use ($uniquePrefix) {
            return strpos($product['name'], $uniquePrefix) === 0;
        });
        $ourProducts = array_values($ourProducts); // Re-index array
        
        $this->assertCount(3, $ourProducts, 'Should have exactly 3 products');
        
        // Verify sorting: 200 > 100 > 50 (descending order)
        $this->assertEquals(200.00, $ourProducts[0]['price']);
        $this->assertEquals(100.00, $ourProducts[1]['price']);
        $this->assertEquals(50.00, $ourProducts[2]['price']);
    }

    /**
     * Test searching products with pagination.
     */
    public function test_can_search_products_with_pagination(): void
    {
        Product::factory()->count(25)->create([
            'currency_id' => $this->currency->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'current_page' => 1,
                    'per_page' => 10,
                ],
            ]);

        $products = $response->json('data.data');
        $this->assertCount(10, $products);
    }

    /**
     * Test searching products with multiple filters.
     */
    public function test_can_search_products_with_multiple_filters(): void
    {
        $eurCurrency = Currency::factory()->create([
            'name' => 'Euro',
            'symbol' => 'EUR',
            'exchange_rate' => 0.85,
        ]);

        Product::factory()->create([
            'name' => 'Laptop Pro',
            'price' => 150.00,
            'tax_cost' => 15.00,
            'manufacturing_cost' => 80.00,
            'currency_id' => $this->currency->id,
        ]);
        Product::factory()->create([
            'name' => 'Laptop Basic',
            'price' => 200.00,
            'tax_cost' => 20.00,
            'manufacturing_cost' => 100.00,
            'currency_id' => $this->currency->id,
        ]);
        Product::factory()->create([
            'name' => 'Desktop Computer',
            'price' => 150.00,
            'tax_cost' => 15.00,
            'manufacturing_cost' => 80.00,
            'currency_id' => $eurCurrency->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?name=Laptop&min_price=100&max_price=180&currency_symbol=USD');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $products = $response->json('data.data');
        $this->assertCount(1, $products);
        $this->assertEquals('Laptop Pro', $products[0]['name']);
    }

    /**
     * Test searching products with invalid currency symbol returns 422.
     */
    public function test_cannot_search_products_with_invalid_currency_symbol(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?currency_symbol=INVALID');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['currency_symbol']);
    }

    /**
     * Test searching products with invalid sort_by returns 422.
     */
    public function test_cannot_search_products_with_invalid_sort_by(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?sort_by=invalid_field');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['sort_by']);
    }

    /**
     * Test searching products with max_price less than min_price returns 422.
     */
    public function test_cannot_search_products_with_invalid_price_range(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/products/search?min_price=200&max_price=100');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['max_price']);
    }
}
