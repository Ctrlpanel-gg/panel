<?php

namespace App\Listeners;

use App\Helpers\CurrencyHelper;
use App\Models\User;
use App\Notifications\ReferralNotification;
use App\Settings\GeneralSettings;
use App\Settings\ReferralSettings;
use App\Settings\UserSettings;
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
    public function __construct(
        UserSettings $user_settings,
        protected ReferralSettings $referralSettings,
        protected GeneralSettings $generalSettings,
        protected CurrencyHelper $currencyHelper,
    ) {
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
        if (!$event->user->email_verified_reward) {
            $event->user->increment('server_limit', $this->server_limit_increment_after_verify_email);
            $event->user->increment('credits', $this->credits_reward_after_verify_email);
            $event->user->update(['email_verified_reward' => true]);
        }

        if (
            $this->referralSettings->require_email_verification &&
            $this->referralSettings->rewardsOnSignUp($event->user)
        ) {
            $referral = DB::table('user_referrals')
                ->where('registered_user_id', $event->user->id)
                ->whereNull('rewarded_at')
                ->whereNull('deleted_at')
                ->first();

            if ($referral && $referral->referral_id) {
                $claimed = DB::table('user_referrals')
                    ->where('registered_user_id', $event->user->id)
                    ->whereNull('rewarded_at')
                    ->whereNull('deleted_at')
                    ->update(['rewarded_at' => now()]);

                if ($claimed) {
                    $ref_user = User::find($referral->referral_id);

                    if ($ref_user) {
                        $ref_user->increment('credits', $this->referralSettings->reward);
                        $ref_user->notify(new ReferralNotification($event->user));

                        activity()
                            ->performedOn($event->user)
                            ->causedBy($ref_user)
                            ->log(sprintf(
                                'gained %s %s for sign-up-referral of %s (ID:%s)',
                                $this->currencyHelper->formatForDisplay($this->referralSettings->reward),
                                $this->generalSettings->credits_display_name,
                                $event->user->name,
                                $event->user->id
                            ));
                    }
                }
            }
        }
    }
}
