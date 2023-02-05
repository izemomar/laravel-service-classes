<?php

namespace App\Services;

use App\Jobs\SendOrderConfirmationEmail;
use \App\Models\Order;
use \App\Models\Product;
use \App\Models\User;

use App\Exceptions\OutOfStockException;
use Illuminate\Support\Collection;

class OrderService
{

    /**
     * Creates a new order.
     *
     * @param array<array<string, mixed>> $orderedProducts The products to be ordered.
     * @param User $user The user making the order.
     *
     * @return Order The created order.
     *
     * @throws OutOfStockException If one of the products is out of stock.
     */
    public function createOrder(array $orderedProducts, User $user)
    {
        // get the list of products from db by id
        $originalProducts = $this->getOriginalProducts($orderedProducts);

        // Calculate the total cost
        $totalOrderCost = $this->calculateTotalOrderCost($originalProducts,  $orderedProducts);

        // Check the inventory and return an error if any product is out of stock
        $this->checkInventoryAndThrowExceptionIfOutOfStock($originalProducts,  $orderedProducts);

        // Deduct from the inventory
        $this->deductInventory($originalProducts,  $orderedProducts);

        // Save the order
        $order = $this->saveOrder($user, $totalOrderCost);

        // Send the confirmation email
        SendOrderConfirmationEmail::dispatch($user, $totalOrderCost);

        return $order;
    }

    /**
     * Gets the original products from the database.
     *
     * @param array<array<string, mixed>> $orderedProducts The products to be ordered.
     *
     * @return Collection The original products.
     */
    private function getOriginalProducts(array $orderedProducts)
    {
        return Product::findMany(array_column($orderedProducts, 'id'));
    }


    /**
     * Calculates the total cost of the order.
     *
     * @param Collection $originalProducts The original products.
     * @param array<array<string, mixed>> $orderedProducts The products to be ordered.
     *
     * @return float The total cost of the order.
     */
    private function calculateTotalOrderCost(Collection $originalProducts, array $orderedProducts)
    {
        return
            $originalProducts->map(function ($product) use ($orderedProducts) {
                $quantity = collect($orderedProducts)->where('id', $product->id)->first()['quantity'];
                return $product->price * $quantity;
            })->sum();
    }


    /**
     * Check the inventory of the products and throw an exception if any product is out of stock.
     *
     * @param \Illuminate\Support\Collection $originalProducts The list of original products.
     * @param array<array<string, mixed>> $orderedProducts The products to be ordered.
     *
     * @throws OutOfStockException If one of the products is out of stock.
     */
    private function checkInventoryAndThrowExceptionIfOutOfStock(Collection $originalProducts, array $orderedProducts)
    {
        $outOfStockProduct =
            $originalProducts->first(function ($product) use ($orderedProducts) {
                $quantity = collect($orderedProducts)->where('id', $product->id)->first()['quantity'];
                return $product->inventory < $quantity;
            });

        if ($outOfStockProduct) {
            throw new OutOfStockException($outOfStockProduct);
        }
    }

    /**
     * @param Collection<Product> $originalProducts
     * @param array<array<string, mixed>> $orderedProducts
     */
    private function deductInventory(Collection $originalProducts, array $orderedProducts)
    {
        $originalProducts->each(function ($product) use ($orderedProducts) {
            $quantity = collect($orderedProducts)->where('id', $product->id)->first()['quantity'];
            $product->inventory = $product->inventory - $quantity;
            $product->save();
        });
    }


    /**
     * Saves a new order.
     *
     * @param User $user The user making the order.
     * @param float $totalOrderCost The total cost of the order.
     *
     * @return Order The saved order.
     */
    private function saveOrder(User $user, float $totalOrderCost): Order
    {
        $order = new Order;
        $order->user_id = $user->id;
        $order->total_cost = $totalOrderCost;
        $order->save();

        return $order;
    }
}
