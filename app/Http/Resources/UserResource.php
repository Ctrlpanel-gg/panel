<?php

namespace App\Http\Resources;

use App\Helpers\CurrencyHelper;
use App\Models\ApplicationApi;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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

        return [
            'id' => $this->id,
            'name' => $this->name,
            'credits' => $this->currencyHelper->convertForDisplay($this->credits),
            'server_limit' => $this->server_limit,
            'avatar' => $this->avatar,
            'suspended' => $this->suspended,
            'email_verified_reward' => $this->email_verified_reward,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'email' => $this->when($canViewSensitiveFields, $this->email),
            'pterodactyl_id' => $this->when($canViewSensitiveFields, $this->pterodactyl_id),
            'ip' => $this->when($canViewSensitiveFields, $this->ip),
            'referral_code' => $this->when($canViewSensitiveFields, $this->referral_code),
            'discord_verified_at' => $this->when($canViewSensitiveFields, $this->discord_verified_at),
            'last_seen' => $this->when($canViewSensitiveFields, $this->last_seen),
            'email_verified_at' => $this->when($canViewSensitiveFields, $this->email_verified_at),
            'servers_count' => $this->whenCounted('servers'),
            'servers_exists' => $this->whenExistsLoaded('servers'),
            'servers' => ServerResource::collection($this->whenLoaded('servers')),
            'notifications_count' => $this->whenCounted('notifications'),
            'notifications_exists' => $this->whenExistsLoaded('notifications'),
            'notifications' => NotificationResource::collection($this->whenLoaded('notifications')),
            'payments_count' => $this->whenCounted('payments'),
            'payments_exists' => $this->whenExistsLoaded('payments'),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'vouchers_count' => $this->whenCounted('vouchers'),
            'vouchers_exists' => $this->whenExistsLoaded('vouchers'),
            'vouchers' => VoucherResource::collection($this->whenLoaded('vouchers')),
            'roles_count' => $this->whenCounted('roles'),
            'roles_exists' => $this->whenExistsLoaded('roles'),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'discord_user' => DiscordUserResource::make($this->whenLoaded('discordUser')),
            'discord_user_exists' => $this->whenExistsLoaded('discordUser'),
            'discord_user_count' => $this->whenCounted('discordUser'),
        ];
    }
}
