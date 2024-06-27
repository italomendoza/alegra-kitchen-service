<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'dish_name',
        'status',
        'recipe_id'
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
