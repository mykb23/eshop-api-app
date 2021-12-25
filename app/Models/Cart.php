<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema (
 *      title="Cart",
 *      type="object",
 *      required={"user_id","product_name","product_id","quantity","price"},
 * )
 */
class Cart extends Model
{
    /**
     *  @OA\Property(type="integer", title="id", default="1",property="id"),
     *  @OA\Property(type="integer", title="user_id", default="1",property="user_id"),
     *  @OA\Property(type="string", title="product_name", default="Gucci Shirt",property="product_name"),
     *  @OA\Property(type="integer", title="product_id", default="1",property="product_id"),
     *  @OA\Property(type="integer", title="quantity", default="1",property="quantity"),
     *  @OA\Property(type="double", title="price", default="199.99",property="price"),
     *
     */
    use HasFactory;

    protected $fillable = ['product_name', 'product_price', 'quantity', 'user_id', 'product_id'];

    protected $table = 'cart';
}
