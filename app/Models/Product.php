<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema (
 *      title="Product",
 *      type="object",
 *      required={"title","description","category","image","price"},
 * )
 */
class Product extends Model
{
    /**
     * @OA\Property(type="integer", title="id", default="1",property="id"),
     * @OA\Property(type="string", title="title", default="Black Shirt", property="title", minLength=5),
     * @OA\Property(type="string", title="slug", default="black-shirt", property="slug"),
     * @OA\Property(type="double", title="price", default="100.00", property="price", minLength=2),
     * @OA\Property(type="string", title="description", default="Black Shirt unisex", property="description", minLength=10),
     * @OA\Property(type="string", title="image", default="black-shirt.jpg", property="image", minLength=5)
     * @OA\Property(type="boolean", title="featured", default="true", property="featured"),
     * @OA\Property(type="string", title="category", default="men's clothing", property="category", minLength=3)
     */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'price',
        'description',
        'image',
        'featured',
        'category',
    ];

    // public function rating()
    // {
    //     return $this->hasMany(Rating::class);
    // }

    public function getImageUrlAttribute()
    {
        return url('images/product' . $this->slug . '/');
    }
}
