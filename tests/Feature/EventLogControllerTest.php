<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\EventLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EventLogControllerTest extends TestCase
{
    use DatabaseTransactions;

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
        $response = $this->getJson('/api/v1/event-logs');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test listing event logs.
     */
    public function test_can_list_event_logs(): void
    {
        // Create some event logs by performing actions
        $currency = Currency::factory()->create();
        $product = Product::factory()->create(['currency_id' => $currency->id]);

        // Create event logs manually for testing
        EventLog::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'event_type' => 'POST',
            'resource_type' => 'Product',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/event-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data',
                    'meta' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Event logs retrieved successfully',
            ]);
    }

    /**
     * Test listing event logs with pagination.
     */
    public function test_can_list_event_logs_with_pagination(): void
    {
        EventLog::factory()->count(25)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/event-logs?per_page=10&page=1');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'meta' => [
                        'current_page' => 1,
                        'per_page' => 10,
                    ],
                ],
            ]);

        $eventLogs = $response->json('data.data');
        $this->assertCount(10, $eventLogs);
    }

    /**
     * Test filtering event logs by event_type.
     */
    public function test_can_filter_event_logs_by_event_type(): void
    {
        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'POST',
            'resource_type' => 'Product',
        ]);

        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'PUT',
            'resource_type' => 'Product',
        ]);

        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'DELETE',
            'resource_type' => 'Product',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/event-logs?event_type=POST');

        $response->assertStatus(200);

        $eventLogs = $response->json('data.data');
        foreach ($eventLogs as $eventLog) {
            $this->assertEquals('POST', $eventLog['event_type']);
        }
    }

    /**
     * Test filtering event logs by resource_type.
     */
    public function test_can_filter_event_logs_by_resource_type(): void
    {
        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'POST',
            'resource_type' => 'Product',
        ]);

        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'POST',
            'resource_type' => 'Currency',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/event-logs?resource_type=Product');

        $response->assertStatus(200);

        $eventLogs = $response->json('data.data');
        foreach ($eventLogs as $eventLog) {
            $this->assertEquals('Product', $eventLog['resource_type']);
        }
    }

    /**
     * Test filtering event logs by user_id.
     */
    public function test_can_filter_event_logs_by_user_id(): void
    {
        $otherUser = User::factory()->create();

        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'POST',
            'resource_type' => 'Product',
        ]);

        EventLog::factory()->create([
            'user_id' => $otherUser->id,
            'event_type' => 'POST',
            'resource_type' => 'Product',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/event-logs?user_id={$this->user->id}");

        $response->assertStatus(200);

        $eventLogs = $response->json('data.data');
        foreach ($eventLogs as $eventLog) {
            $this->assertEquals($this->user->id, $eventLog['user_id']);
        }
    }

    /**
     * Test sorting event logs.
     */
    public function test_can_sort_event_logs(): void
    {
        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'POST',
            'resource_type' => 'Product',
            'created_at' => now()->subDays(2),
        ]);

        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'PUT',
            'resource_type' => 'Product',
            'created_at' => now()->subDays(1),
        ]);

        EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'DELETE',
            'resource_type' => 'Product',
            'created_at' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/event-logs?sort_by=created_at&sort_order=desc');

        $response->assertStatus(200);

        $eventLogs = $response->json('data.data');
        if (count($eventLogs) >= 3) {
            // Verify descending order (newest first)
            $this->assertEquals('DELETE', $eventLogs[0]['event_type']);
        }
    }

    /**
     * Test showing a specific event log.
     */
    public function test_can_show_event_log(): void
    {
        $eventLog = EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'POST',
            'resource_type' => 'Product',
            'resource_id' => 123,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/event-logs/{$eventLog->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user',
                    'user_id',
                    'event_type',
                    'resource_type',
                    'resource_id',
                    'endpoint',
                    'method',
                    'data',
                    'ip_address',
                    'user_agent',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Event log retrieved successfully',
                'data' => [
                    'id' => $eventLog->id,
                    'event_type' => 'POST',
                    'resource_type' => 'Product',
                    'resource_id' => 123,
                ],
            ]);
    }

    /**
     * Test showing a non-existent event log returns 404.
     */
    public function test_showing_non_existent_event_log_returns_404(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/event-logs/999');

        $response->assertStatus(404);
    }

    /**
     * Test event log includes user information when loaded.
     */
    public function test_event_log_includes_user_information(): void
    {
        $eventLog = EventLog::factory()->create([
            'user_id' => $this->user->id,
            'event_type' => 'POST',
            'resource_type' => 'Product',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/v1/event-logs/{$eventLog->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotNull($data['user']);
        $this->assertEquals($this->user->id, $data['user']['id']);
        $this->assertEquals($this->user->name, $data['user']['name']);
        $this->assertEquals($this->user->email, $data['user']['email']);
    }
}
