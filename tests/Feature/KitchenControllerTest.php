<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Recipe;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class KitchenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepare_dish_success()
    {
        $recipe = Recipe::factory()->create();
        $recipe->ingredients()->attach([
            1 => ['quantity' => 2],
            2 => ['quantity' => 3]
        ]);

        Http::fake([
            'warehouse-service/api/ingredients/check' => Http::response(['available' => true]),
            'warehouse-service/api/ingredients/decrement' => Http::response(['success' => true])
        ]);

        $response = $this->postJson('/api/kitchen/prepare-dish');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Dish prepared successfully',
                 ]);

        // check status order
        $this->assertDatabaseHas('orders', [
            'status' => 'completed',
            'dish_name' => $recipe->name
        ]);
    }

    public function test_prepare_dish_ingredients_not_available()
    {
        $recipe = Recipe::factory()->create();
        $recipe->ingredients()->attach([
            1 => ['quantity' => 2],
            2 => ['quantity' => 3]
        ]);

        Http::fake([
            'warehouse-service/api/ingredients/check' => Http::response(['available' => false]),
        ]);

        $response = $this->postJson('/api/kitchen/prepare-dish');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'order_id',
                 ]);
        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Ingredients not available, order is pending',
                 ]);

        $orderId = $response->json('order_id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'pending'
        ]);

    }

    public function test_retry_pending_orders()
    {
        $recipe = Recipe::factory()->create();
        $order = Order::factory()->create([
            'recipe_id' => $recipe->id,
            'status' => 'pending',
        ]);

        Http::fake([
            'warehouse-service/api/ingredients/check' => Http::response(['available' => true]),
            'warehouse-service/api/ingredients/decrement' => Http::response(['success' => true])
        ]);

        $response = $this->postJson('/api/kitchen/retry-pending-orders');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Retried pending orders',
                 ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed'
        ]);
    }

    public function test_get_orders_in_preparation()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->getJson('/api/kitchen/orders-in-preparation');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $order->id]);
    }

    public function test_get_order_history()
    {
        $order = Order::factory()->create();

        $response = $this->getJson('/api/kitchen/order-history');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $order->id]);
    }

    public function test_get_recipes()
    {
        $recipe = Recipe::factory()->create();

        $response = $this->getJson('/api/kitchen/recipes');

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $recipe->id]);
    }
}
