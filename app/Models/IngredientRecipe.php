<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngredientRecipe extends Model
{
    use HasFactory;

    protected $table = 'ingredient_recipe'; // Nombre de la tabla pivot

    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity'
    ];

    // Relación muchos a uno con Recipe
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    // Relación muchos a uno con Ingredient
    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
