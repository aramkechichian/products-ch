<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API endpoints for user authentication"
 * )
 */
class AuthController extends Controller
{
    /**
     * Get the authenticated user.
     *
     * @OA\Get(
     *     path="/api/v1/auth/me",
     *     summary="Get authenticated user",
     *     description="Returns the currently authenticated user information",
     *     operationId="getMe",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()),
            'User retrieved successfully'
        );
    }

    /**
     * Logout the user (revoke current token).
     *
     * @OA\Post(
     *     path="/api/v1/auth/logout-all",
     *     summary="Logout from all devices",
     *     description="Revokes all authentication tokens for the current user",
     *     operationId="logoutAll",
     *     tags={"Authentication"},
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out from all devices successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logged out from all devices successfully"),
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
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->success(
            null,
            'Logged out from all devices successfully'
        );
    }
}
