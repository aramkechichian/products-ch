<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\CurrencyController;
use App\Http\Controllers\Api\V1\EventLogController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductPriceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for version 1 of your API.
| These routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
});

// Protected authentication routes
Route::prefix('auth')->middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
});

// Currencies routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('currencies', CurrencyController::class);
    
    // Products routes - search and export must be before apiResource to avoid route conflicts
    Route::get('products/search', [ProductController::class, 'search']);
    Route::get('products/export', [ProductController::class, 'export']);
    Route::apiResource('products', ProductController::class);
    
    // Product Prices routes (nested under products)
    Route::get('products/{product}/prices', [ProductPriceController::class, 'index']);
    Route::post('products/{product}/prices', [ProductPriceController::class, 'store']);
    
    // Product Prices export route (must be before nested routes to avoid conflicts)
    Route::get('product-prices/export', [ProductPriceController::class, 'export']);
    
    // Event Logs routes (export must be before show to avoid route conflicts)
    Route::get('event-logs/export', [EventLogController::class, 'export']);
    Route::get('event-logs', [EventLogController::class, 'index']);
    Route::get('event-logs/{eventLog}', [EventLogController::class, 'show']);
});
