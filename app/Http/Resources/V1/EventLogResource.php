<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;

class EventLogResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'user_id' => $this->user_id,
            'event_type' => $this->event_type,
            'resource_type' => $this->resource_type,
            'resource_id' => $this->resource_id,
            'endpoint' => $this->endpoint,
            'method' => $this->method,
            'data' => $this->data ?? [], // Incluye todo el payload completo del request
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
