<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;

class ProductPriceResource extends BaseResource
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
            'product_id' => $this->product_id,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'currency_id' => $this->currency_id,
            'price' => (float) $this->price,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
