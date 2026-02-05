<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    use HasFactory;

    protected $table = 'events_log';

    protected $fillable = [
        'user_id',
        'event_type',
        'resource_type',
        'resource_id',
        'endpoint',
        'method',
        'data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that performed the event.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Event types constants.
     */
    public const EVENT_TYPE_CREATE = 'POST';
    public const EVENT_TYPE_UPDATE = 'PUT';
    public const EVENT_TYPE_DELETE = 'DELETE';
}
