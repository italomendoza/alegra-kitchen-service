<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/hola-mundo', function () {
    return response()->json(['mensaje' => 'kitchen-service']);
});

use App\Http\Controllers\KitchenController;

Route::post('/kitchen/prepare-dish', [KitchenController::class, 'prepareDish']);
Route::post('/kitchen/retry-pending-orders', [KitchenController::class, 'retryPendingOrders']);
Route::get('/kitchen/orders-in-preparation', [KitchenController::class, 'getOrdersInPreparation']);
Route::get('/kitchen/recipes', [KitchenController::class, 'getRecipes']);
Route::get('/kitchen/order-history', [KitchenController::class, 'getOrderHistory']);
Route::get('/kitchen/recipes', [KitchenController::class, 'getRecipes']);
