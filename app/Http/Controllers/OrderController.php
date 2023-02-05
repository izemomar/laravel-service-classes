<?php

namespace App\Http\Controllers;

use App\Exceptions\OutOfStockException;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Exception;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, OrderService $orderService)
    {

        try {

            $validatedProducts = $request->validated('products');

            // Create the order using the OrderService
            $order = $orderService->createOrder($validatedProducts, $request->user());

            // Return a success response with the created order
            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order
            ], 201);
        } catch (OutOfStockException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'error' => "The order couldn't be created"
            ], 500);
        }
    }
}
