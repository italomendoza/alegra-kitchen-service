<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

class KitchenController extends Controller
{
    public function prepareDish(Request $request)
    {
        $order = new Order();


        // choise random recipe
        $recipe = Recipe::inRandomOrder()->first();
        // $recipe = Recipe::where('id', 1)->first();
        $order->dish_name = $recipe->name;
        $order->status = 'pending';
        $order->save();
        $orderId = $order->id;
        if (!$recipe) {
            return response()->json(['message' => 'No recipes available'], 404);
        }

        $order->recipe_id = $recipe->id;
        $order->save();

        $allIngredientsAvailable = $this->checkAndDecrementIngredients($recipe);

        if ($allIngredientsAvailable) {
            $order->status = 'completed';
            $order->dish_name = $recipe->name;
            $order->save();

            return response()->json(['message' => 'Dish prepared successfully']);
        } else {
            $order->status = 'pending';
            $order->save();

            return response()->json([
                'message' => 'Ingredients not available, order is pending',
                'order_id' => $orderId,
            ]);
        }
    }

    private function checkAndDecrementIngredients($recipe)
    {
        $allIngredientsAvailable = true;
        $ingredientsToCheck = [];

        foreach ($recipe->ingredients as $ingredient) {
            $ingredientsToCheck[] = [
                'ingredient_name' => $ingredient->name,
                'quantity' => $ingredient->pivot->quantity
            ];
        }

        $response = Http::post('http://warehouse-service/api/ingredients/check', [
            'ingredients' => $ingredientsToCheck
        ]);

        if ($response->failed() || $response->json()['available'] == false) {
            $allIngredientsAvailable = false;
        } else {
            Http::post('http://warehouse-service/api/ingredients/decrement', [
                'ingredients' => $ingredientsToCheck
            ]);
        }

        return $allIngredientsAvailable;
    }



    public function retryPendingOrders()
    {
        $pendingOrders = Order::where('status', 'pending')->get();

        foreach ($pendingOrders as $order) {
            $recipe = Recipe::find($order->recipe_id);

            if ($recipe) {
                $allIngredientsAvailable = $this->checkAndDecrementIngredients($recipe);

                if ($allIngredientsAvailable) {
                    $order->status = 'completed';
                    $order->dish_name = $recipe->name;
                    $order->save();
                }
            }
        }

        return response()->json(['message' => 'Retried pending orders']);
    }

    public function inProgress()
    {
        // get all orders in preparation
        $orders = Order::where('status', 'in preparation')->get();
        return response()->json($orders);
    }


    public function getOrdersInPreparation()
    {
        // get all orders in preparation
        $ordersInPreparation = Order::where('status', 'pending')->get();

        return response()->json($ordersInPreparation);
    }
    public function getOrderHistory()
    {
        // get all orders
        $orderHistory = Order::with('recipe')->get();

        return response()->json($orderHistory);
    }
    public function getRecipes()
    {
        // get all recipes
        $recipes = Recipe::with('ingredients')->get();

        return response()->json($recipes);
    }
}
