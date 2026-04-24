<?php

namespace App\Http\Resources;

use App\Helpers\CurrencyHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class VoucherResource extends JsonResource
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
            'code' => $this->code,
            'memo' => $this->memo,
            'credits' => $this->currencyHelper->convertForDisplay($this->credits),
            'uses' => $this->uses,
            'expires_at' => $this->expires_at ? $this->expires_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'users_count' => $this->whenCounted('users'),
            'users_exists' => $this->whenExistsLoaded('users'),
            'users' => UserResource::newCollection($this->whenLoaded('users')),
        ];
    }
}
