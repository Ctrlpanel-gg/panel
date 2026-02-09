<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NodeResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'location_id' => $this->location_id,
            'disabled' => $this->disabled,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'location_count' => $this->whenCounted('location'),
            'location_exists' => $this->whenExistsLoaded('location'),
            'location' => LocationResource::make($this->whenLoaded('location')),
        ];
    }
}
