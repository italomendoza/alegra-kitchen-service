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


        // Seleccionar una receta aleatoria
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

        $response = Http::post('https://api-warehouse.oloi.dev/api/ingredients/check', [
            'ingredients' => $ingredientsToCheck
        ]);

        if ($response->failed() || $response->json()['available'] == false) {
            $allIngredientsAvailable = false;
        } else {
            Http::post('https://api-warehouse.oloi.dev/api/ingredients/decrement', [
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
        // Obtener pedidos en preparación
        $orders = Order::where('status', 'in preparation')->get();
        return response()->json($orders);
    }

    public function history()
    {
        // Obtener historial de pedidos
        $orders = Order::all();
        return response()->json($orders);
    }



    public function getOrdersInPreparation()
    {
        // Obtener todas las órdenes que están en preparación
        $ordersInPreparation = Order::where('status', 'pending')->get();

        // Devolver las órdenes en un formato JSON
        return response()->json($ordersInPreparation);
    }
    public function getOrderHistory()
    {
        // Obtener todo el historial de pedidos
        $orderHistory = Order::with('recipe')->get();

        // Devolver el historial de pedidos en un formato JSON
        return response()->json($orderHistory);
    }
    public function getRecipes()
    {
        // Obtener todas las recetas con sus ingredientes
        $recipes = Recipe::with('ingredients')->get();

        // Devolver las recetas en un formato JSON
        return response()->json($recipes);
    }
}
