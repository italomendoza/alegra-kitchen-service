<?php

namespace Database\Seeders;

// database/seeders/RecipesSeeder.php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipesSeeder extends Seeder
{
    public function run()
    {
        $recipes = [
            ['name' => 'Tomato Soup'],
            ['name' => 'Lemon Chicken'],
            ['name' => 'Potato Salad'],
            ['name' => 'Rice and Meat'],
            ['name' => 'Cheese Sandwich'],
            ['name' => 'Chicken Lettuce Wrap'],
        ];

        DB::table('recipes')->insert($recipes);

        $recipeIngredients = [
            ['recipe_id' => 1, 'ingredient_id' => 1, 'quantity' => 2], // Tomato Soup - 2 tomatoes
            ['recipe_id' => 1, 'ingredient_id' => 7, 'quantity' => 1], // Tomato Soup - 1 onion
            ['recipe_id' => 2, 'ingredient_id' => 2, 'quantity' => 1], // Lemon Chicken - 1 lemon
            ['recipe_id' => 2, 'ingredient_id' => 10, 'quantity' => 1], // Lemon Chicken - 1 chicken
            ['recipe_id' => 3, 'ingredient_id' => 3, 'quantity' => 3], // Potato Salad - 3 potatoes
            ['recipe_id' => 3, 'ingredient_id' => 7, 'quantity' => 1], // Potato Salad - 1 onion
            ['recipe_id' => 4, 'ingredient_id' => 4, 'quantity' => 2], // Rice and Meat - 2 rice
            ['recipe_id' => 4, 'ingredient_id' => 9, 'quantity' => 1], // Rice and Meat - 1 meat
            ['recipe_id' => 4, 'ingredient_id' => 5, 'quantity' => 1], // Rice and Meat - 1 meat
            ['recipe_id' => 5, 'ingredient_id' => 8, 'quantity' => 1], // Cheese Sandwich - 1 cheese
            ['recipe_id' => 5, 'ingredient_id' => 1, 'quantity' => 1], // Cheese Sandwich - 1 tomato
            ['recipe_id' => 6, 'ingredient_id' => 6, 'quantity' => 1], // Chicken Lettuce Wrap - 1 lettuce
            ['recipe_id' => 6, 'ingredient_id' => 10, 'quantity' => 1], // Chicken Lettuce Wrap - 1 chicken
        ];

        DB::table('ingredient_recipe')->insert($recipeIngredients);
    }
}

