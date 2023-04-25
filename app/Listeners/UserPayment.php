<?php

namespace App\Listeners;

use App\Events\PaymentEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\PartnerDiscount;
use App\Settings\GeneralSettings;
use App\Settings\ReferralSettings;
use App\Settings\UserSettings;

class UserPayment
{
    private $server_limit_after_irl_purchase;

    private $referral_mode;

    private $referral_percentage;

    private $referral_always_give_commission;

    private $credits_display_name;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(UserSettings $user_settings, ReferralSettings $referral_settings, GeneralSettings $general_settings)
    {
        $this->server_limit_after_irl_purchase = $user_settings->server_limit_after_irl_purchase;
        $this->referral_mode = $referral_settings->mode;
        $this->referral_percentage = $referral_settings->percentage;
        $this->referral_always_give_commission = $referral_settings->always_give_commission;
        $this->credits_display_name = $general_settings->credits_display_name;
    }
    
    /**
     * Handle the event.
     *
     * @param  \App\Events\PaymentEvent  $event
     * @return void
     */
    public function handle(PaymentEvent $event)
    {
        $user = $event->user;
        $shopProduct = $event->shopProduct;

        // only update user if payment is paid
        if ($event->payment->status != "paid") {
            return;
        }

        //update server limit
        if ($this->server_limit_after_irl_purchase !== 0 && $user->server_limit < $this->server_limit_after_irl_purchase) {
            $user->update(['server_limit' => $this->server_limit_after_irl_purchase]);
        }

        //update User with bought item
        if ($shopProduct->type == "Credits") {
            $user->increment('credits', $shopProduct->quantity);
        } elseif ($shopProduct->type == "Server slots") {
            $user->increment('server_limit', $shopProduct->quantity);
        }

        //give referral commission always
        if (($this->referral_mode === "commission" || $this->referral_mode === "both") && $shopProduct->type == "Credits" && $this->referral_always_give_commission) {
            if ($ref_user = DB::table("user_referrals")->where('registered_user_id', '=', $user->id)->first()) {
                $ref_user = User::findOrFail($ref_user->referral_id);
                $increment = number_format($shopProduct->quantity * (PartnerDiscount::getCommission($ref_user->id, $this->referral_percentage)) / 100, 0, "", "");
                $ref_user->increment('credits', $increment);

                //LOGS REFERRALS IN THE ACTIVITY LOG
                activity()
                    ->performedOn($user)
                    ->causedBy($ref_user)
                    ->log('gained ' . $increment . ' ' . $this->credits_display_name . ' for commission-referral of ' . $user->name . ' (ID:' . $user->id . ')');
            }
        }
        //update role give Referral-reward
        if ($user->role == 'member') {
            $user->update(['role' => 'client']);

            //give referral commission only on first purchase
            if (($this->referral_mode === "commission" || $this->referral_mode === "both") && $shopProduct->type == "Credits" && !$this->referral_always_give_commission) {
                if ($ref_user = DB::table("user_referrals")->where('registered_user_id', '=', $user->id)->first()) {
                    $ref_user = User::findOrFail($ref_user->referral_id);
                    $increment = number_format($shopProduct->quantity * (PartnerDiscount::getCommission($ref_user->id, $this->referral_percentage)) / 100, 0, "", "");
                    $ref_user->increment('credits', $increment);

                    //LOGS REFERRALS IN THE ACTIVITY LOG
                    activity()
                        ->performedOn($user)
                        ->causedBy($ref_user)
                        ->log('gained ' . $increment . ' ' . $this->credits_display_name . ' for commission-referral of ' . $user->name . ' (ID:' . $user->id . ')');
                }
            }
        }

        // LOGS PAYMENT IN THE ACTIVITY LOG
        activity()
            ->performedOn($user)
            ->causedBy($user)
            ->log('bought ' . $shopProduct->quantity . ' ' . $shopProduct->type . ' for ' . $shopProduct->price . $shopProduct->currency_code);
    }
}
