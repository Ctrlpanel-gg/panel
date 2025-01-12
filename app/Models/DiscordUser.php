<?php

namespace App\Models;

use App\Settings\DiscordSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;

class DiscordUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'username',
        'avatar',
        'discriminator',
        'public_flags',
        'flags',
        'locale',
        'mfa_enabled',
        'premium_type',
        'email',
        'verified',
    ];

    public $incrementing = false;

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return 'https://cdn.discordapp.com/avatars/'.$this->id.'/'.$this->avatar.'.png';
    }


    /**
     * Add or remove role on discord server
     * @param string $action The action to perform (add or remove)
     * @param string $role_id The Role ID to add or remove
     * @return mixed
     */
    public function addOrRemoveRole(string $action, string $role_id): mixed
    {
        $discordSettings = app(DiscordSettings::class);
        return match ($action) {
            'add' => Http::withHeaders(
                [
                    'Authorization' => 'Bot ' . $discordSettings->bot_token,
                    'Content-Type' => 'application/json',
                    'X-Audit-Log-Reason' => 'Role added by panel'
                ]
            )->put(
                "https://discord.com/api/guilds/{$discordSettings->guild_id}/members/{$this->id}/roles/{$discordSettings->role_id}",
                ['access_token' => $discordSettings->bot_token]
            ),
            'remove' => Http::withHeaders(
                [
                    'Authorization' => 'Bot ' . $discordSettings->bot_token,
                    'Content-Type' => 'application/json',
                    'X-Audit-Log-Reason' => 'Role removed by panel'
                ]
            )->delete(
                "https://discord.com/api/guilds/{$discordSettings->guild_id}/members/{$this->id}/roles/{$discordSettings->role_id}",
                ['access_token' => $discordSettings->bot_token]
            ),
            default => null,
        };
    }
}
