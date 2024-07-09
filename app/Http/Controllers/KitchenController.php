<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Log;

class KitchenController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function prepareDish()
    {
        try {
            $order = new Order();

            // Seleccionar una receta aleatoria
            // $recipe = Recipe::inRandomOrder()->first();
            $recipe = Recipe::where('id', '=', '1')->first();

            if (!$recipe) {
                return response()->json(['message' => 'No recipes available'], 404);
            }

            $order->dish_name = $recipe->name;
            $order->status = 'pending';
            $order->recipe_id = $recipe->id;
            $order->save();

            // Preparar los ingredientes para enviar
            // Enviar mensaje a RabbitMQ
            $this->orderService->prepareIngredients($recipe, $order);

            return response()->json([
                'message' => 'Order received and is being processed',
                'order_id' => $order->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error preparing dish: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to process the order, please contact the restaurant administrator'], 500);
        }
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

    private function prepareIngredients($recipe)
    {
        $ingredients = [];
        foreach ($recipe->ingredients as $ingredient) {
            $ingredients[] = [
                'ingredient_name' => $ingredient->name,
                'quantity' => $ingredient->pivot->quantity
            ];
        }
        return $ingredients;
    }
}
