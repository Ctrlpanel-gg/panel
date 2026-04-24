<?php

namespace App\Http\Resources;

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
        return [
            'id' => $this->id,
            'username' => $this->username,
            'discriminator' => $this->discriminator,
            'avatar' => $this->avatar,
            'email' => $this->email,
            'verified' => $this->verified,
            'public_flags' => $this->public_flags,
            'flags' => $this->flags,
            'locale' => $this->locale,
            'premium_type' => $this->premium_type,
            'mfa_enabled' => $this->mfa_enabled,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
