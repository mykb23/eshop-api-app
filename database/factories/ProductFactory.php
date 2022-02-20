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
            'price' => $this->faker->randomFloat(2, 0, 999.99),
            'description' => $this->faker->text(),
            "image" => "https://res.cloudinary.com/mykb/image/upload/v1643027274/e-com-app/images/products/default/no-image.png",
            'featured' => $this->faker->numberBetween(0, 1, false),
            'category' => $this->faker->word(),
        ];
    }
}
