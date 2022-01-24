<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'slug' =>    $this->faker->sentence(3),
            'price' => $this->faker->numberBetween(20, 1000),
            'description' => $this->faker->text(),
            "image" => "https://res.cloudinary.com/mykb/image/upload/v1643027274/e-com-app/images/products/default/no-image.png",
            'featured' => $this->faker->numberBetween(0, 1),
            'category' => $this->faker->words(),
        ];
    }
}
