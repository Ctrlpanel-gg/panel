<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServerResource extends JsonResource
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
            'suspended' => $this->suspended,
            'identifier' => $this->identifier,
            'billing_priority' => $this->billing_priority,
            'pterodactyl_id' => $this->pterodactyl_id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'canceled' => $this->canceled ? $this->canceled->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'last_billed' => $this->last_billed->toDateTimeString(),
            'product_count' => $this->whenCounted('product'),
            'product_exists' => $this->whenExistsLoaded('product'),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'user_count' => $this->whenCounted('user'),
            'user_exists' => $this->whenExistsLoaded('user'),
            'user' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
