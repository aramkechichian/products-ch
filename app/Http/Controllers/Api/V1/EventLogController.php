<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\EventLogResource;
use App\Models\EventLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Event Logs",
 *     description="API endpoints for viewing system event logs"
 * )
 */
class EventLogController extends Controller
{
    /**
     * Display a paginated listing of event logs.
     *
     * @OA\Get(
     *     path="/api/v1/event-logs",
     *     summary="Get all event logs",
     *     description="Returns a paginated list of all system event logs (POST, PUT, DELETE operations)",
     *     operationId="getEventLogs",
     *     tags={"Event Logs"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1, default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of results per page (1-100)",
     *         required=false,
     *         @OA\Schema(type="integer", example=15, default=15)
     *     ),
     *     @OA\Parameter(
     *         name="event_type",
     *         in="query",
     *         description="Filter by event type (POST, PUT, DELETE)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"POST", "PUT", "DELETE"}, example="POST")
     *     ),
     *     @OA\Parameter(
     *         name="resource_type",
     *         in="query",
     *         description="Filter by resource type (Product, Currency)",
     *         required=false,
     *         @OA\Schema(type="string", example="Product")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort by field (created_at, event_type, resource_type)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"created_at", "event_type", "resource_type"}, example="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, example="desc")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event logs retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event logs retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/EventLog")
     *                 ),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=100),
     *                 @OA\Property(property="last_page", type="integer", example=7)
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
        $eventLog = app(EventLog::class);
        $query = $eventLog->newQuery()->with('user');

        // Filter by event_type
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Filter by resource_type
        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->resource_type);
        }

        // Filter by user_id
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = min($request->input('per_page', 15), 100); // Max 100 per page
        $eventLogs = $query->paginate($perPage);

        return $this->success(
            EventLogResource::collection($eventLogs)->response()->getData(true),
            'Event logs retrieved successfully'
        );
    }

    /**
     * Display the specified event log.
     *
     * @OA\Get(
     *     path="/api/v1/event-logs/{id}",
     *     summary="Get a specific event log",
     *     description="Returns a single event log by its ID",
     *     operationId="getEventLog",
     *     tags={"Event Logs"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Event Log ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event log retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Event log retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/EventLog")
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
     *         description="Event log not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function show(EventLog $eventLog): JsonResponse
    {
        $eventLog->load('user');

        return $this->success(
            new EventLogResource($eventLog),
            'Event log retrieved successfully'
        );
    }
}
