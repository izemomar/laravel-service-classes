<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Exceptions\OutOfStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Support\Collection;


class OrderServiceTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @var OrderService */
    private $service;

    /** @var User */
    private $user;

    /** @var Collection */
    private $originalProducts;

    /** @var array */
    private $orderedProducts;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->service = new OrderService;

        $this->originalProducts = Product::factory(3)->create([
            'inventory' => 10
        ]);

        $this->orderedProducts = [
            [
                'id' => $this->originalProducts[0]->id,
                'quantity' => 2
            ],
            [
                'id' => $this->originalProducts[1]->id,
                'quantity' => 5
            ],
            [
                'id' => $this->originalProducts[2]->id,
                'quantity' => 3
            ],
        ];
    }

    /** @test */
    public function createOrderShouldReturnsOrder()
    {
        $order = $this->service->createOrder($this->orderedProducts, $this->user);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'id' => $order->id
        ]);
    }

    /** @test */
    public function createOrderShouldDeductsInventory()
    {
        $this->service->createOrder($this->orderedProducts, $this->user);

        $this->originalProducts->each(function ($product) {
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'inventory' => $product->inventory - collect($this->orderedProducts)->where('id', $product->id)->first()['quantity']
            ]);
        });
    }

    /** @test */
    public function createOrderShouldThrowsExceptionIfProductIsOutOfStock()
    {
        $this->expectException(OutOfStockException::class);

        $this->originalProducts->first()->update([
            'inventory' => 1
        ]);

        $this->service->createOrder($this->orderedProducts, $this->user);
    }
}
