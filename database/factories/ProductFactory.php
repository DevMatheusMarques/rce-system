<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {;
        $productName = 'Produto - ' . \Str::random(5);
        $pictureName = str_replace(' ', '-', $productName);
        $arrayEnumCategory = ['toner', 'paper', 'form', 'cartridge', 'ribbon', 'desk', 'others'];
        return [
            'name' => $productName,
            'description' => $this->faker->text(50),
            'category' => $this->faker->randomElement($arrayEnumCategory),
            'minimum' => 5,
            'stock' => $this->faker->numberBetween(2, 30),
            'processing' => 0,
            'picture_path' => '/assets/img/products/' . $pictureName . '.webp',
            'status' => $this->faker->randomElement(['active', 'active', 'inactive']),
        ];
    }
}
