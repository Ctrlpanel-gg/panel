<?php

namespace App\Actions;

use App\Helpers\CurrencyHelper;
use App\Models\User;
use App\Notifications\ReferralNotification;
use App\Settings\GeneralSettings;
use App\Settings\ReferralSettings;
use Illuminate\Support\Facades\DB;

class ProcessReferralAction
{
    public function __construct(
        protected ReferralSettings $referralSettings,
        protected GeneralSettings $generalSettings,
        protected CurrencyHelper $currencyHelper,
    )
    {}

    public function execute(User $user, string $referral_code, bool $log_activity = false)
    {
        return DB::transaction(function () use ($user, $referral_code, $log_activity) {
            $refUser = User::query()
                ->where('referral_code', $referral_code)
                ->lockForUpdate()
                ->first();

            if ($refUser === null || $refUser->id === $user->id) {
                return false;
            }

            $existingReferral = DB::table('user_referrals')
                ->where('registered_user_id', $user->id)
                ->exists();

            if ($existingReferral) {
                return false;
            }

            DB::table('user_referrals')->insert([
                'referral_id' => $refUser->id,
                'registered_user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($this->referralSettings->mode === 'sign-up' || $this->referralSettings->mode === 'both') {
                $refUser->increment('credits', $this->referralSettings->reward);

                if ($log_activity) {
                    $log = sprintf(
                        'gained %s %s for sign-up-referral of %s (ID:%s)',
                        $this->currencyHelper->formatForDisplay($this->referralSettings->reward),
                        $this->generalSettings->credits_display_name,
                        $user->name,
                        $user->id
                    );

                    activity()
                        ->performedOn($user)
                        ->causedBy($refUser)
                        ->log($log);
                }

                DB::afterCommit(static function () use ($refUser, $user): void {
                    $refUser->notify(new ReferralNotification($user));
                });
            }

            return true;
        });
    }
}
