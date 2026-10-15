<?php

namespace App\Http\Controllers;

use App\Models\PartnerDiscount;
use App\Models\UsefulLink;
use App\Settings\GeneralSettings;
use App\Settings\WebsiteSettings;
use App\Settings\ReferralSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    const TIME_LEFT_BG_SUCCESS = 'bg-success';
    const TIME_LEFT_BG_WARNING = 'bg-warning';
    const TIME_LEFT_BG_DANGER = 'bg-danger';

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @description Get the Background Color for the Days-Left-Box in HomeView
     *
     * @param  float  $daysLeft
     * @return string
     */
    public function getTimeLeftBoxBackground(float $daysLeft): string
    {
        if ($daysLeft >= 15) {
            return self::TIME_LEFT_BG_SUCCESS;
        }
        if ($daysLeft <= 7) {
            return self::TIME_LEFT_BG_DANGER;
        }

        return self::TIME_LEFT_BG_WARNING;
    }

    /**
     * @description Get the Text for the Days-Left-Box in HomeView
     *
     * @param  float  $daysLeft
     * @param  float  $hoursLeft
     * @return string
     */
    public function getTimeLeftBoxText(float $daysLeft, float $hoursLeft)
    {
        if ($hoursLeft < 1) {
            return __('You ran out of Credits');
        }

        $fullDays = (int) floor($daysLeft);
        $remainingHours = (int) ceil($hoursLeft - ($fullDays * 24));

        if ($remainingHours >= 24) {
            $fullDays++;
            $remainingHours = 0;
        }
        if ($fullDays > 0 && $remainingHours > 0) {
            return strval(number_format($fullDays, 0)) . __('d') . ' ' . strval(number_format($remainingHours, 0)) . __('h');
        }

        if ($fullDays > 0) {
            return strval(number_format($fullDays, 0)) . __('d');
        }

        return strval(number_format($hoursLeft, 0)) . __('h');
    }

    /** Show the application dashboard. */
    public function index(GeneralSettings $general_settings, WebsiteSettings $website_settings, ReferralSettings $referral_settings)
    {
        $user = Auth::user();
        $usage = $user->creditUsage();
        $credits = $user->credits;
        $bg = '';
        $boxText = '';
        $timeLeft = null;

        /** Build our Time-Left-Box */
        if ($credits > 0 && $usage > 0) {
            $daysLeft = $credits / ($usage / 30);
            $hoursLeft = $credits / ($usage / 30 / 24);

            $bg = $this->getTimeLeftBoxBackground($daysLeft);
            $boxText = $this->getTimeLeftBoxText($daysLeft, $hoursLeft);

            if ($daysLeft > 1) {
                $estimatedDate = Carbon::now()->addDays((int) ceil($daysLeft));
            } else {
                $estimatedDate = Carbon::now()->addHours((int) ceil($hoursLeft));
            }

            $timeLeft = [
                'bg' => $bg,
                'message' => __('Estimated run out: :date', ['date' => $estimatedDate->format('d-m-Y H:i')]),
                'date' => $estimatedDate->toDateString(),
                'value' => $boxText,
            ];
        }

        // RETURN ALL VALUES
        return view('home')->with([
            'usage' => $usage,
            'credits' => $credits,
            'useful_links_dashboard' => UsefulLink::where("position","like","%dashboard%")->get()->sortby("id"),
            'bg' => $bg,
            'boxText' => $boxText,
            'numberOfReferrals' => DB::table('user_referrals')->where('referral_id', '=', $user->id)->count(),
            'partnerDiscount' => PartnerDiscount::where('user_id', $user->id)->first(),
            'myDiscount' => PartnerDiscount::getDiscount(),
            'timeLeft' => $timeLeft,
            'general_settings' => $general_settings,
            'website_settings' => $website_settings,
            'referral_settings' => $referral_settings
        ]);
    }
}
