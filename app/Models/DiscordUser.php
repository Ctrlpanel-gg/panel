<?php

namespace App\Models;

use App\Settings\DiscordSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Exception;

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
    public function addOrRemoveRole(string $action, string $role_id)
    {
        $discordSettings = app(DiscordSettings::class);

        $url = "https://discord.com/api/guilds/{$discordSettings->guild_id}/members/{$this->id}/roles/{$role_id}";

        try {
            $response = match ($action) {
                'add' => Http::withHeaders(
                    [
                        'Authorization' => 'Bot ' . $discordSettings->bot_token,
                        'Content-Type' => 'application/json',
                        'X-Audit-Log-Reason' => 'Role added by panel'
                    ]
                )->put($url),
                'remove' => Http::withHeaders(
                    [
                        'Authorization' => 'Bot ' . $discordSettings->bot_token,
                        'Content-Type' => 'application/json',
                        'X-Audit-Log-Reason' => 'Role removed by panel'
                    ]
                )->delete($url),
                default => null
            };

            if ($response->failed()) {
                throw new Exception(
                    "Discord API error: {$response->status()} - " .
                    ($response->json('message') ?? 'Unknown error')
                );
            }


            $activity = activity()
                ->performedOn($this->user)
                ->log('was added to role ' . $role_id . " on Discord");

            $causer = auth()->user();
            if ($causer instanceof \App\Models\User && $causer->can('admin.users.write')) {
                $activity->causer_id = $causer->id;
                $activity->causer_type = get_class($causer);
                $activity->save();
            } else {
                $activity->causer_id = null;
                $activity->causer_type = null;
                $activity->save();
            }

            return true;
        } catch (\Exception $e) {
            logger()->error($e->getMessage());

            return false;
        }
    }
}
