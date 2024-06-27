<?php

namespace Database\Seeders;
// database/seeders/KitchenIngredientsSeeder.php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KitchenIngredientsSeeder extends Seeder
{
    public function run()
    {
        $ingredients = [
            ['name' => 'tomato'],
            ['name' => 'lemon'],
            ['name' => 'potato'],
            ['name' => 'rice'],
            ['name' => 'ketchup'],
            ['name' => 'lettuce'],
            ['name' => 'onion'],
            ['name' => 'cheese'],
            ['name' => 'meat'],
            ['name' => 'chicken'],
        ];
        

        DB::table('ingredients')->insert($ingredients);
    }
}
