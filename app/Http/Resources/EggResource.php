<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EggResource extends JsonResource
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
            'docker_image' => $this->docker_image,
            'startup' => $this->startup,
            'environment' => $this->environment,
            'nest_id' => $this->nest_id,
            'disabled' => $this->disabled,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'nest_count' => $this->whenCounted('nest'),
            'nest_exists' => $this->whenExistsLoaded('nest'),
            'nest' => NestResource::make($this->whenLoaded('nest')),
        ];
    }
}
