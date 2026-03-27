<?php

namespace App\Listeners;

use App\Settings\UserSettings;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class Verified
{
    private $server_limit_increment_after_verify_email;
    private $credits_reward_after_verify_email;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(UserSettings $user_settings)
    {
        $this->server_limit_increment_after_verify_email = $user_settings->server_limit_increment_after_verify_email;
        $this->credits_reward_after_verify_email = $user_settings->credits_reward_after_verify_email;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        DB::transaction(function () use ($event): void {
            $user = User::query()->whereKey($event->user->id)->lockForUpdate()->first();

            if (! $user || $user->email_verified_reward) {
                return;
            }

            $user->increment('server_limit', $this->server_limit_increment_after_verify_email);
            $user->increment('credits', $this->credits_reward_after_verify_email);
            $user->update(['email_verified_reward' => true]);
        });
    }
}
