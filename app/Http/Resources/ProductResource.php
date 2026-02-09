<?php

namespace App\Http\Resources;

use App\Helpers\CurrencyHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ProductResource extends JsonResource
{
    protected CurrencyHelper $currencyHelper;

    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
        $this->currencyHelper = app(CurrencyHelper::class);
    }

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
            'price' => $this->currencyHelper->convertForDisplay($this->price),
            'memory' => $this->memory,
            'cpu' => $this->cpu,
            'swap' => $this->swap,
            'disk' => $this->disk,
            'io' => $this->io,
            'databases' => $this->databases,
            'backups' => $this->backups,
            'serverlimit' => $this->serverlimit,
            'allocations' => $this->allocations,
            'oom_killer' => $this->oom_killer,
            'default_billing_priority' => $this->default_billing_priority,
            'disabled' => $this->disabled,
            'minimum_credits' => $this->minimum_credits ? $this->currencyHelper->convertForDisplay($this->minimum_credits) : null,
            'billing_period' => $this->billing_period,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'nodes_count' => $this->whenCounted('nodes'),
            'nodes_exists' => $this->whenExistsLoaded('nodes'),
            'nodes' => NodeResource::collection($this->whenLoaded('nodes')),
            'eggs_count' => $this->whenCounted('eggs'),
            'eggs_exists' => $this->whenExistsLoaded('eggs'),
            'eggs' => EggResource::collection($this->whenLoaded('eggs')),
            'servers_count' => $this->whenCounted('servers'),
            'servers_exists' => $this->whenExistsLoaded('servers'),
            'servers' => ServerResource::collection($this->whenLoaded('servers')),
        ];
    }
}
