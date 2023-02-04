<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_cost',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * we can also have a relationship with the products table
     * but since we're using this code for educational purposes
     * we can ignore this relationship
     */
    /* public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('inventory');
    } */
}
