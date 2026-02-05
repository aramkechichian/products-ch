<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\SearchProductRequest;
use App\Http\Requests\V1\StoreProductRequest;
use App\Http\Requests\V1\UpdateProductRequest;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API endpoints for managing products"
 * )
 */
class ProductController extends Controller
{
    /**
     * Display a listing of products.
     *
     * @OA\Get(
     *     path="/api/v1/products",
     *     summary="Get all products",
     *     description="Returns a list of all products with their currency information",
     *     operationId="getProducts",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Product")
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
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('currency')
            ->orderBy('name')
            ->get();

        return $this->success(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }

    /**
     * Store a newly created product.
     *
     * @OA\Post(
     *     path="/api/v1/products",
     *     summary="Create a new product",
     *     description="Creates a new product with the provided data. The currency_id must exist in the currencies table.",
     *     operationId="storeProduct",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description", "price", "currency_id"},
     *             @OA\Property(property="name", type="string", example="Laptop"),
     *             @OA\Property(property="description", type="string", example="High-performance laptop for professionals"),
     *             @OA\Property(property="price", type="number", format="float", example=1299.99),
     *             @OA\Property(property="currency_id", type="integer", example=1),
     *             @OA\Property(property="tax_cost", type="number", format="float", example=100.00),
     *             @OA\Property(property="manufacturing_cost", type="number", format="float", example=800.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
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
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->load('currency');

        return $this->success(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified product.
     *
     * @OA\Get(
     *     path="/api/v1/products/{id}",
     *     summary="Get a product by ID",
     *     description="Returns a single product by its ID with currency information",
     *     operationId="getProduct",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
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
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::with('currency')->findOrFail($id);

        return $this->success(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    /**
     * Update the specified product.
     *
     * @OA\Put(
     *     path="/api/v1/products/{id}",
     *     summary="Update a product",
     *     description="Updates an existing product with the provided data. The currency_id must exist in the currencies table if provided.",
     *     operationId="updateProduct",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Laptop"),
     *             @OA\Property(property="description", type="string", example="High-performance laptop for professionals"),
     *             @OA\Property(property="price", type="number", format="float", example=1299.99),
     *             @OA\Property(property="currency_id", type="integer", example=1),
     *             @OA\Property(property="tax_cost", type="number", format="float", example=100.00),
     *             @OA\Property(property="manufacturing_cost", type="number", format="float", example=800.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Product")
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
     *             @OA\Property(property="message", type="string", example="Product not found")
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
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());
        $product->load('currency');

        return $this->success(
            new ProductResource($product),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product.
     *
     * @OA\Delete(
     *     path="/api/v1/products/{id}",
     *     summary="Delete a product",
     *     description="Deletes a product by its ID",
     *     operationId="deleteProduct",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Product deleted successfully"),
     *             @OA\Property(property="data", type="null")
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
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return $this->success(
            null,
            'Product deleted successfully'
        );
    }

    /**
     * Search products with advanced filters.
     *
     * @OA\Get(
     *     path="/api/v1/products/search",
     *     summary="Search products with advanced filters",
     *     description="Search products using multiple filters: name, currency, price range, tax cost, manufacturing cost, and sorting options",
     *     operationId="searchProducts",
     *     tags={"Products"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Search by product name (partial match)",
     *         required=false,
     *         @OA\Schema(type="string", example="Laptop")
     *     ),
     *     @OA\Parameter(
     *         name="currency_symbol",
     *         in="query",
     *         description="Filter by currency symbol (e.g., USD, EUR)",
     *         required=false,
     *         @OA\Schema(type="string", example="USD")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=100.00)
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=2000.00)
     *     ),
     *     @OA\Parameter(
     *         name="min_tax_cost",
     *         in="query",
     *         description="Minimum tax cost",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=10.00)
     *     ),
     *     @OA\Parameter(
     *         name="max_tax_cost",
     *         in="query",
     *         description="Maximum tax cost",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=200.00)
     *     ),
     *     @OA\Parameter(
     *         name="min_manufacturing_cost",
     *         in="query",
     *         description="Minimum manufacturing cost",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=50.00)
     *     ),
     *     @OA\Parameter(
     *         name="max_manufacturing_cost",
     *         in="query",
     *         description="Maximum manufacturing cost",
     *         required=false,
     *         @OA\Schema(type="number", format="float", example=1000.00)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field (name, price, tax_cost, manufacturing_cost, created_at, updated_at)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"name", "price", "tax_cost", "manufacturing_cost", "created_at", "updated_at"}, example="price")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order (asc or desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page (1-100)",
     *         required=false,
     *         @OA\Schema(type="integer", example=15, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1, default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products found successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Products found successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Product")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="last_page", type="integer", example=4)
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
    public function search(SearchProductRequest $request): JsonResponse
    {
        $query = Product::query()->with('currency');

        // Filter by name (partial match, case-insensitive)
        if ($request->filled('name')) {
            $query->where('name', 'ILIKE', '%' . $request->name . '%');
        }

        // Filter by currency symbol
        if ($request->filled('currency_symbol')) {
            $query->whereHas('currency', function ($q) use ($request) {
                $q->where('symbol', $request->currency_symbol);
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by tax_cost range
        if ($request->filled('min_tax_cost')) {
            $query->where('tax_cost', '>=', $request->min_tax_cost);
        }
        if ($request->filled('max_tax_cost')) {
            $query->where('tax_cost', '<=', $request->max_tax_cost);
        }

        // Filter by manufacturing_cost range
        if ($request->filled('min_manufacturing_cost')) {
            $query->where('manufacturing_cost', '>=', $request->min_manufacturing_cost);
        }
        if ($request->filled('max_manufacturing_cost')) {
            $query->where('manufacturing_cost', '<=', $request->max_manufacturing_cost);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return $this->success(
            [
                'data' => ProductResource::collection($products->items()),
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
            'Products found successfully'
        );
    }
}
