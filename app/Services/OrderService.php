<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Recipe;
use App\Services\RabbitMQService;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $rabbitMQService;

    public function __construct(RabbitMQService $rabbitMQService)
    {
        $this->rabbitMQService = $rabbitMQService;
    }

    public function completeOrder($orderId)
    {
        $order = Order::find($orderId);

        if ($order) {
            $order->status = 'completed';
            $order->save();
        } else {
            Log::error('Order not found: ' . $orderId);
        }
    }

    public function retryPendingOrders()
    {
        $pendingOrders = Order::where('status', 'pending')->get();

        foreach ($pendingOrders as $order) {
            $recipe = Recipe::find($order->recipe_id);

            if ($recipe) {
                // Preparar los ingredientes para enviar
                $this->prepareIngredients($recipe, $order);
            } else {
                Log::error('Recipe not found for order: ' . $order->id);
            }
        }

        return true;
    }

    public function prepareIngredients($recipe, $order)
    {
        $ingredients = [];
        foreach ($recipe->ingredients as $ingredient) {
            $ingredients[] = [
                'ingredient_name' => $ingredient->name,
                'quantity' => $ingredient->pivot->quantity
            ];
        }

        $message = [
            'order_id' => $order->id,
            'ingredients' => $ingredients
        ];

        try {
            $this->rabbitMQService->sendMessage('ingredient_verification_queue', $message);
        } catch (\Exception $e) {
            Log::error('Failed to send message to RabbitMQ: ' . $e->getMessage());
        }

        return true;
    }
}
