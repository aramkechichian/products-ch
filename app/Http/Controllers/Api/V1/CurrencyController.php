<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\V1\StoreCurrencyRequest;
use App\Http\Requests\V1\UpdateCurrencyRequest;
use App\Http\Resources\V1\CurrencyResource;
use App\Models\Currency;
use App\Services\EventLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Currencies",
 *     description="API endpoints for managing currencies"
 * )
 */
class CurrencyController extends Controller
{
    /**
     * Display a listing of currencies.
     *
     * @OA\Get(
     *     path="/api/v1/currencies",
     *     summary="Get all currencies",
     *     description="Returns a list of all currencies",
     *     operationId="getCurrencies",
     *     tags={"Currencies"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of currencies",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Currencies retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Currency")
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
        $currency = app(Currency::class);
        $currencies = $currency->newQuery()
            ->orderBy('name')
            ->get();

        return $this->success(
            CurrencyResource::collection($currencies),
            'Currencies retrieved successfully'
        );
    }

    /**
     * Store a newly created currency.
     *
     * @OA\Post(
     *     path="/api/v1/currencies",
     *     summary="Create a new currency",
     *     description="Creates a new currency with the provided data",
     *     operationId="storeCurrency",
     *     tags={"Currencies"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "symbol", "exchange_rate"},
     *             @OA\Property(property="name", type="string", example="US Dollar"),
     *             @OA\Property(property="symbol", type="string", example="USD"),
     *             @OA\Property(property="exchange_rate", type="number", format="float", example=1.0000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Currency created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Currency created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Currency")
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
    public function store(StoreCurrencyRequest $request, EventLogService $eventLogService): JsonResponse
    {
        $currency = app(Currency::class);
        $newCurrency = $currency->create($request->validated());

        // Log the event
        $eventLogService->logCreate('Currency', $newCurrency->id, $request);

        return $this->success(
            new CurrencyResource($newCurrency),
            'Currency created successfully',
            201
        );
    }

    /**
     * Display the specified currency.
     *
     * @OA\Get(
     *     path="/api/v1/currencies/{id}",
     *     summary="Get a currency by ID",
     *     description="Returns a single currency by its ID",
     *     operationId="getCurrency",
     *     tags={"Currencies"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Currency ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Currency retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Currency retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Currency")
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
     *         description="Currency not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Currency not found")
     *         )
     *     )
     * )
     */
    public function show(Currency $currency): JsonResponse
    {
        return $this->success(
            new CurrencyResource($currency),
            'Currency retrieved successfully'
        );
    }

    /**
     * Update the specified currency.
     *
     * @OA\Put(
     *     path="/api/v1/currencies/{id}",
     *     summary="Update a currency",
     *     description="Updates an existing currency with the provided data",
     *     operationId="updateCurrency",
     *     tags={"Currencies"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Currency ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="US Dollar"),
     *             @OA\Property(property="symbol", type="string", example="USD"),
     *             @OA\Property(property="exchange_rate", type="number", format="float", example=1.0000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Currency updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Currency updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Currency")
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
     *         description="Currency not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Currency not found")
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
    public function update(UpdateCurrencyRequest $request, Currency $currency, EventLogService $eventLogService): JsonResponse
    {
        $currency->update($request->validated());

        // Log the event
        $eventLogService->logUpdate('Currency', $currency->id, $request);

        return $this->success(
            new CurrencyResource($currency),
            'Currency updated successfully'
        );
    }

    /**
     * Remove the specified currency.
     *
     * @OA\Delete(
     *     path="/api/v1/currencies/{id}",
     *     summary="Delete a currency",
     *     description="Deletes a currency by its ID",
     *     operationId="deleteCurrency",
     *     tags={"Currencies"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Currency ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Currency deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Currency deleted successfully"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Currency not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Currency not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Currency cannot be deleted (has associated products)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Cannot delete currency because it has associated products")
     *         )
     *     )
     * )
     */
    public function destroy(Currency $currency, Request $request, EventLogService $eventLogService): JsonResponse
    {
        // Verificar si tiene productos asociados
        if ($currency->products()->count() > 0) {
            return $this->error(
                'Cannot delete currency because it has associated products',
                409
            );
        }

        $currencyId = $currency->id;
        $currency->delete();

        // Log the event
        $eventLogService->logDelete('Currency', $currencyId, $request);

        return $this->success(
            null,
            'Currency deleted successfully'
        );
    }
}
