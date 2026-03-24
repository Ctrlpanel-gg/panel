<?php

namespace App\Http\Resources;

use App\Helpers\CurrencyHelper;
use App\Models\ApplicationApi;
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
        /** @var ApplicationApi|null $apiToken */
        $apiToken = $request->attributes->get('apiToken');
        $canViewSensitiveFields = ! $apiToken || $apiToken->hasAbility(ApplicationApi::ABILITY_USERS_SENSITIVE);

        $data = [
            'id' => $this->id,
            'code' => $this->code,
            'memo' => $this->memo,
            'credits' => $this->currencyHelper->convertForDisplay($this->credits),
            'uses' => $this->uses,
            'expires_at' => $this->expires_at ? $this->expires_at->toDateTimeString() : null,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];

        if ($canViewSensitiveFields) {
            $data['users_count'] = $this->whenCounted('users');
            $data['users_exists'] = $this->whenExistsLoaded('users');
            if ($this->relationLoaded('users')) {
                $data['users'] = UserResource::collection($this->users);
            }
        }

        return $data;
    }
}
