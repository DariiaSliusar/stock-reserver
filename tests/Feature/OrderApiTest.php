<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_with_valid_data(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/orders', [
            'sku' => 'TEST-SKU-123',
            'qty' => 5,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'sku',
                    'qty',
                    'status',
                    'status_label',
                    'supplier_ref',
                    'created_at',
                    'updated_at',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'sku' => 'TEST-SKU-123',
                    'qty' => 5,
                    'status' => 'pending',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'sku' => 'TEST-SKU-123',
            'qty' => 5,
            'status' => 'pending',
        ]);
    }

    public function test_validation_fails_without_sku(): void
    {
        $response = $this->postJson('/api/orders', [
            'qty' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    public function test_validation_fails_without_qty(): void
    {
        $response = $this->postJson('/api/orders', [
            'sku' => 'TEST-SKU',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qty']);
    }

    public function test_validation_fails_with_invalid_qty(): void
    {
        $response = $this->postJson('/api/orders', [
            'sku' => 'TEST-SKU',
            'qty' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qty']);

        $response = $this->postJson('/api/orders', [
            'sku' => 'TEST-SKU',
            'qty' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['qty']);
    }

    public function test_can_get_order_by_id(): void
    {
        $order = Order::query()->create([
            'sku' => 'TEST-SKU-456',
            'qty' => 10,
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'sku',
                    'qty',
                    'status',
                    'status_label',
                    'supplier_ref',
                    'created_at',
                    'updated_at',
                    'movements',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $order->id,
                    'sku' => 'TEST-SKU-456',
                    'qty' => 10,
                    'status' => 'pending',
                ],
            ]);
    }

    public function test_returns_404_for_non_existent_order(): void
    {
        $response = $this->getJson('/api/orders/99999');

        $response->assertStatus(404);
    }

    public function test_can_get_inventory_movements(): void
    {
        $inventory = Inventory::query()->create([
            'sku' => 'TEST-SKU-789',
            'qty_available' => 100,
            'qty_reserved' => 0,
        ]);

        $response = $this->getJson('/api/inventory/TEST-SKU-789/movements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'sku',
                    'qty_available',
                    'qty_reserved',
                    'movements',
                ],
                'meta' => [
                    'sku',
                    'total',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'sku' => 'TEST-SKU-789',
                    'qty_available' => 100,
                    'qty_reserved' => 0,
                ],
            ]);
    }

    public function test_returns_404_for_non_existent_inventory(): void
    {
        $response = $this->getJson('/api/inventory/NON-EXISTENT-SKU/movements');

        $response->assertStatus(404);
    }
}

