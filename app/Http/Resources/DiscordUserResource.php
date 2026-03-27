<?php

namespace App\Http\Resources;

use App\Models\ApplicationApi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscordUserResource extends JsonResource
{
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
            'username' => $this->username,
            'avatar' => $this->avatar,
            'verified' => $this->verified,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];

        if ($canViewSensitiveFields) {
            $data['discriminator'] = $this->discriminator;
            $data['email'] = $this->email;
            $data['public_flags'] = $this->public_flags;
            $data['flags'] = $this->flags;
            $data['locale'] = $this->locale;
            $data['premium_type'] = $this->premium_type;
            $data['mfa_enabled'] = $this->mfa_enabled;
        }

        return $data;
    }
}
