<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'description' => $this->description,
            'expected_delivery' => $this->expected_delivery?->toDateString() ?? $this->expected_delivery,
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'mechanic' => new UserResource($this->whenLoaded('mechanic')),
            'items' => ServiceItemResource::collection($this->whenLoaded('items')),
            'items_total' => $this->whenLoaded('items', fn () => (float) $this->items->sum('cost')),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
