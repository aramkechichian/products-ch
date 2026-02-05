<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\ProductPricesExport;
use App\Http\Requests\V1\StoreProductPriceRequest;
use App\Http\Resources\V1\ProductPriceResource;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Services\EventLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @OA\Tag(
 *     name="Product Prices",
 *     description="API endpoints for managing product prices in different currencies"
 * )
 */
class ProductPriceController extends Controller
{
    /**
     * Display a listing of prices for a specific product.
     *
     * @OA\Get(
     *     path="/api/v1/products/{product}/prices",
     *     summary="Get all prices for a product",
     *     description="Returns a list of all prices for a product in different currencies",
     *     operationId="getProductPrices",
     *     tags={"Product Prices"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product prices retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product prices retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProductPrice")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function index(Product $product): JsonResponse
    {
        $productPrice = app(ProductPrice::class);
        $prices = $productPrice->newQuery()
            ->where('product_id', $product->id)
            ->with('currency')
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->success(
            ProductPriceResource::collection($prices),
            'Product prices retrieved successfully'
        );
    }

    /**
     * Store or update a product price in a different currency.
     *
     * @OA\Post(
     *     path="/api/v1/products/{product}/prices",
     *     summary="Create or update a product price in a different currency",
     *     description="Creates or updates a price for a product in a different currency. The price is calculated as: product.price * currency.exchange_rate. If a price already exists for this product and currency, it will be overwritten.",
     *     operationId="storeProductPrice",
     *     tags={"Product Prices"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="product",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"currency_id"},
     *             @OA\Property(property="currency_id", type="integer", example=2, description="Currency ID (must be different from product's base currency)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product price created or updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product price created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductPrice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product price created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product price created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/ProductPrice")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreProductPriceRequest $request, Product $product, EventLogService $eventLogService): JsonResponse
    {
        // Obtener la moneda destino
        $targetCurrency = \App\Models\Currency::findOrFail($request->currency_id);

        // Calcular el precio: price * exchange_rate
        $convertedPrice = $product->price * $targetCurrency->exchange_rate;

        // Verificar si ya existe un precio para este producto en esta moneda
        $existingPrice = ProductPrice::where('product_id', $product->id)
            ->where('currency_id', $request->currency_id)
            ->first();

        $isUpdate = $existingPrice !== null;

        // Crear o actualizar el ProductPrice
        $productPrice = ProductPrice::updateOrCreate(
            [
                'product_id' => $product->id,
                'currency_id' => $request->currency_id,
            ],
            [
                'price' => round($convertedPrice, 2), // Redondear a 2 decimales
            ]
        );

        // Cargar relaciones
        $productPrice->load('currency');

        // Log the event
        if ($isUpdate) {
            $eventLogService->logUpdate('ProductPrice', $productPrice->id, $request, [
                'calculated_price' => $convertedPrice,
                'base_price' => $product->price,
                'exchange_rate' => $targetCurrency->exchange_rate,
            ]);
            $message = 'Product price updated successfully';
            $statusCode = 200;
        } else {
            $eventLogService->logCreate('ProductPrice', $productPrice->id, $request, [
                'calculated_price' => $convertedPrice,
                'base_price' => $product->price,
                'exchange_rate' => $targetCurrency->exchange_rate,
            ]);
            $message = 'Product price created successfully';
            $statusCode = 201;
        }

        return $this->success(
            new ProductPriceResource($productPrice),
            $message,
            $statusCode
        );
    }

    /**
     * Export all product prices to Excel.
     *
     * @OA\Get(
     *     path="/api/v1/product-prices/export",
     *     summary="Export all product prices to Excel",
     *     description="Downloads an Excel file containing all product prices with product name, currency name, and price",
     *     operationId="exportProductPrices",
     *     tags={"Product Prices"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Excel file download",
     *         @OA\MediaType(
     *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
     *             @OA\Schema(
     *                 type="string",
     *                 format="binary"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function export()
    {
        $fileName = 'product_prices_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new ProductPricesExport, $fileName);
    }
}
