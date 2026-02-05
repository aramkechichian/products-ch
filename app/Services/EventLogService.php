<?php

namespace App\Services;

use App\Models\EventLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EventLogService
{
    /**
     * Log an event to the events_log table.
     *
     * @param string $eventType POST, PUT, or DELETE
     * @param string $resourceType Model name (e.g., 'Product', 'Currency')
     * @param int|null $resourceId ID of the affected resource
     * @param Request $request The HTTP request
     * @param array|null $additionalData Additional data to store
     * @return EventLog
     */
    public function logEvent(
        string $eventType,
        string $resourceType,
        ?int $resourceId,
        Request $request,
        ?array $additionalData = null
    ): ?EventLog {
        error_log("=== logEvent START ===");
        error_log("EventType: {$eventType}, ResourceType: {$resourceType}, ResourceID: " . ($resourceId ?? 'null'));
        
        try {
            Log::info('=== EVENT LOG DEBUG START ===');
            Log::info('Event Type: ' . $eventType);
            Log::info('Resource Type: ' . $resourceType);
            Log::info('Resource ID: ' . ($resourceId ?? 'null'));
            
            $user = Auth::user();
            $userId = $user?->id;
            $userEmail = $user?->email ?? 'null';
            error_log("User: ID {$userId} - {$userEmail}");
            Log::info('User: ' . ($user ? "ID {$user->id} - {$user->email}" : 'null'));
            
            // Verificar si la tabla existe
            $tableExists = Schema::hasTable('events_log');
            error_log("Table events_log exists: " . ($tableExists ? 'YES' : 'NO'));
            Log::info('Table events_log exists: ' . ($tableExists ? 'YES' : 'NO'));
            
            if (!$tableExists) {
                error_log("ERROR: Table events_log does not exist! Please run migrations.");
                Log::error('Table events_log does not exist! Please run migrations.');
                return null;
            }
            
            $data = [
                'payload' => $request->all(),
            ];

            if ($additionalData) {
                $data = array_merge($data, $additionalData);
            }

            error_log("Attempting to create EventLog...");
            Log::info('Attempting to create EventLog...');
            
            $eventLogData = [
                'user_id' => $user?->id,
                'event_type' => $eventType,
                'resource_type' => $resourceType,
                'resource_id' => $resourceId,
                'endpoint' => $request->path(),
                'method' => $request->method(),
                'data' => $data,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];
            
            error_log("EventLog data: " . json_encode($eventLogData));
            
            $eventLog = EventLog::create($eventLogData);
            
            error_log("SUCCESS: EventLog created with ID: {$eventLog->id}");
            Log::info('EventLog created successfully! ID: ' . $eventLog->id);
            Log::info('=== EVENT LOG DEBUG END ===');
            
            return $eventLog;
        } catch (\Exception $e) {
            error_log("EXCEPTION in logEvent: " . $e->getMessage());
            error_log("Exception class: " . get_class($e));
            error_log("File: " . $e->getFile() . ":" . $e->getLine());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Log the error but don't fail the request
            Log::error('=== EVENT LOG ERROR ===');
            Log::error('Failed to log event: ' . $e->getMessage());
            Log::error('Exception class: ' . get_class($e));
            Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== END EVENT LOG ERROR ===');
            return null;
        }
    }

    /**
     * Log a CREATE event (POST).
     *
     * @param string $resourceType
     * @param int $resourceId
     * @param Request $request
     * @param array|null $additionalData
     * @return EventLog
     */
    public function logCreate(string $resourceType, int $resourceId, Request $request, ?array $additionalData = null): ?EventLog
    {
        error_log("=== EventLogService::logCreate CALLED ===");
        error_log("Resource: {$resourceType}, ID: {$resourceId}");
        Log::info("📝 EventLogService::logCreate called - Resource: {$resourceType}, ID: {$resourceId}");
        $result = $this->logEvent(EventLog::EVENT_TYPE_CREATE, $resourceType, $resourceId, $request, $additionalData);
        error_log("EventLogService::logCreate RESULT: " . ($result ? "SUCCESS ID: {$result->id}" : "NULL"));
        return $result;
    }

    /**
     * Log an UPDATE event (PUT).
     *
     * @param string $resourceType
     * @param int $resourceId
     * @param Request $request
     * @param array|null $additionalData
     * @return EventLog
     */
    public function logUpdate(string $resourceType, int $resourceId, Request $request, ?array $additionalData = null): ?EventLog
    {
        Log::info("📝 EventLogService::logUpdate called - Resource: {$resourceType}, ID: {$resourceId}");
        return $this->logEvent(EventLog::EVENT_TYPE_UPDATE, $resourceType, $resourceId, $request, $additionalData);
    }

    /**
     * Log a DELETE event.
     *
     * @param string $resourceType
     * @param int $resourceId
     * @param Request $request
     * @param array|null $additionalData
     * @return EventLog
     */
    public function logDelete(string $resourceType, int $resourceId, Request $request, ?array $additionalData = null): ?EventLog
    {
        Log::info("📝 EventLogService::logDelete called - Resource: {$resourceType}, ID: {$resourceId}");
        return $this->logEvent(EventLog::EVENT_TYPE_DELETE, $resourceType, $resourceId, $request, $additionalData);
    }
}
