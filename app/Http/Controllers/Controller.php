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
 *
 * @OA\Schema(
 *     schema="EventLog",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user", type="object", nullable=true,
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", example="john@example.com")
 *     ),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="event_type", type="string", example="POST", enum={"POST", "PUT", "DELETE"}),
 *     @OA\Property(property="resource_type", type="string", example="Product"),
 *     @OA\Property(property="resource_id", type="integer", nullable=true, example=123),
 *     @OA\Property(property="endpoint", type="string", example="/api/v1/products"),
 *     @OA\Property(property="method", type="string", example="POST"),
 *     @OA\Property(property="data", type="object", description="Complete payload data including all request data. Contains the full request payload and any additional data.",
 *         @OA\Property(property="payload", type="object", description="Complete request payload with all fields sent in the request",
 *             example={"name": "Product Name", "description": "Product description", "price": 99.99, "currency_id": 1, "tax_cost": 10.00, "manufacturing_cost": 50.00}
 *         )
 *     ),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.1"),
 *     @OA\Property(property="user_agent", type="string", example="Mozilla/5.0..."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
