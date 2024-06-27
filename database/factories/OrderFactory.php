<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'recipe_id' => Recipe::factory(),
            'dish_name' => $this->faker->word,
            'status' => 'pending', // Puedes ajustar esto según sea necesario
            // Asegúrate de incluir otros campos necesarios para Order si los hay
        ];
    }
}
