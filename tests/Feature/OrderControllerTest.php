<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        \App\Models\User::factory()->create();
    }

    /**
     * @test
     */
    public function shouldCreateANewOrder()
    {
        $product = \App\Models\Product::factory()->create([
            'inventory' => 5
        ]);

        $data = [
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 3
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(201);
        $this->assertCount(1, Order::all());
        $this->assertEquals(2, Product::first()->inventory);
    }

    /**
     * @test
     */
    public function shouldReturnAnErrorWhenProductIsOutOfStock()
    {
        $product = \App\Models\Product::factory(Product::class)->create([
            'inventory' => 3
        ]);

        $data = [
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 4
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(400);
        $this->assertCount(0, Order::all());
        $this->assertEquals(3, Product::first()->inventory);
    }

    /**
     * @test
     */
    public function shouldValidatesInputData()
    {
        $product = \App\Models\Product::factory(Product::class)->create([
            'inventory' => 5
        ]);

        $data = [
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => -1
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['products.0.quantity']);
        $this->assertCount(0, Order::all());
        $this->assertEquals(5, Product::first()->inventory);
    }
}
