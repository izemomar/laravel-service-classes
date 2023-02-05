<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * @param \App\Models\Product $product - The product that's out of stock.
     */
    public function __construct(\App\Models\Product $product)
    {
        parent::__construct("Product {$product->name} is out of stock.");
    }
}
