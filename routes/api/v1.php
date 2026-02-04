<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
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

// Example: Product routes (commented out until we create the controller)
// Route::prefix('products')->middleware('auth:sanctum')->group(function () {
//     Route::get('/', [\App\Http\Controllers\Api\V1\ProductController::class, 'index']);
//     Route::post('/', [\App\Http\Controllers\Api\V1\ProductController::class, 'store']);
//     Route::get('/{id}', [\App\Http\Controllers\Api\V1\ProductController::class, 'show']);
//     Route::put('/{id}', [\App\Http\Controllers\Api\V1\ProductController::class, 'update']);
//     Route::delete('/{id}', [\App\Http\Controllers\Api\V1\ProductController::class, 'destroy']);
// });
