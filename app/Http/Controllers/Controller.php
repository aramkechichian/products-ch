<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Products API",
 *     version="1.0.0",
 *     description="API REST profesional para gestión de productos",
 *     @OA\Contact(
 *         email="support@example.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your bearer token in the format: Bearer {token}"
 * )
 *
 * @OA\Schema(
 *     schema="Currency",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="US Dollar"),
 *     @OA\Property(property="symbol", type="string", example="USD"),
 *     @OA\Property(property="exchange_rate", type="number", format="float", example=1.0000),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Laptop"),
 *     @OA\Property(property="description", type="string", example="High-performance laptop"),
 *     @OA\Property(property="price", type="number", format="float", example=1299.99),
 *     @OA\Property(property="currency", ref="#/components/schemas/Currency"),
 *     @OA\Property(property="currency_id", type="integer", example=1),
 *     @OA\Property(property="tax_cost", type="number", format="float", example=100.00),
 *     @OA\Property(property="manufacturing_cost", type="number", format="float", example=800.00),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
