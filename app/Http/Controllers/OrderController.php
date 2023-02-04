<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $validatedProducts = $request->validated('products');

        // get the list of products from db by id
        $products = Product::findMany(array_column($validatedProducts, 'id'));


        // Calculate the total cost
        $totalOrderCost = $products->map(function ($product) use ($validatedProducts) {
            $quantity = collect($validatedProducts)->where('id', $product->id)->first()['quantity'];
            return $product->price * $quantity;
        })->sum();




        // Check the inventory and return an error if any product is out of stock
        $outOfStockProduct = $products->first(function ($product) use ($validatedProducts) {
            $quantity = collect($validatedProducts)->where('id', $product->id)->first()['quantity'];
            return $product->inventory < $quantity;
        });

        if ($outOfStockProduct) {
            return response()->json([
                'error' => 'Product ' . $outOfStockProduct->name . ' is out of stock'
            ], 400);
        }


        // Deduct from the inventory
        $products->each(function ($product) use ($validatedProducts) {
            $quantity = collect($validatedProducts)->where('id', $product->id)->first()['quantity'];
            $product->inventory = $product->inventory - $quantity;
            $product->save();
        });

        // Save the order
        $order = new Order;
        $order->user_id = $request->user()->id;
        $order->total_cost = $totalOrderCost;
        $order->save();

        // Send the confirmation email
        SendOrderConfirmationEmail::dispatch($request->user(), $totalOrderCost);

        return response()->json([
            'message' => 'Order created successfully'
        ], 201);
    }
}
