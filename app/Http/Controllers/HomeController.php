<?php

namespace App\Http\Controllers;

use App\Models\PartnerDiscount;
use App\Models\UsefulLink;
use App\Settings\GeneralSettings;
use App\Settings\WebsiteSettings;
use App\Settings\ReferralSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
     * Calculate when user will run out of credits. Holy shit what have i done? for just 1 fucking box on the dashboard?
     */
    protected function calculateCreditRunout($user, $credits)
    {
        $servers = $user->getServersWithProduct();
        if ($servers->isEmpty()) {
            return [
                'run_out_date' => null,
                'simulation_steps' => []
            ];
        }

        // Prepare all servers: get next billing date and price (in credits)
        $serverStates = [];
        foreach ($servers as $server) {
            $product = $server->product;
            $period = $product->billing_period;
            $price = $product->price;
            $lastBilled = $server->last_billed ? Carbon::parse($server->last_billed) : now();
            $nextBilling = $lastBilled->copy();
            while ($nextBilling->lessThanOrEqualTo(now())) {
                switch ($period) {
                    case 'hourly': $nextBilling->addHour(); break;
                    case 'daily': $nextBilling->addDay(); break;
                    case 'weekly': $nextBilling->addWeek(); break;
                    case 'monthly': $nextBilling->addMonth(); break;
                    case 'quarterly': $nextBilling->addMonths(3); break;
                    case 'half-annually': $nextBilling->addMonths(6); break;
                    case 'annually': $nextBilling->addYear(); break;
                }
            }
            $serverStates[] = [
                'server' => $server,
                'product' => $product,
                'period' => $period,
                'price' => $price,
                'nextBilling' => $nextBilling
            ];
        }

        $simulationSteps = [];
        $currentCredits = $credits;
        $runOutDate = null;
        $maxSteps = 1000; // max steps to generate events. Good accuracy for most cases, prevents infinite loops.
        $step = 0;

        while ($step < $maxSteps) {
            // Find the next billing date among all servers
            $nextDates = array_map(fn($s) => $s['nextBilling'], $serverStates);
            $minDate = collect($nextDates)->min();
            // Find all servers that bill at this date
            $dueServers = array_filter($serverStates, fn($s) => $s['nextBilling']->equalTo($minDate));
            $sum = 0;
            $actions = [];
            foreach ($dueServers as $idx => $s) {
                $sum += $s['price'];
                $actions[] = $s['product']->name . ' (' . $s['period'] . ')';
            }
            if ($currentCredits < $sum) {
                $runOutDate = $minDate;
                break;
            }
            $currentCredits -= $sum;
            $simulationSteps[] = [
                'date' => $minDate->format('Y-m-d H:i:s'),
                'action' => implode(' + ', $actions),
                'amount' => -$sum,
                'remaining' => $currentCredits,
                'details' => ''
            ];
            // Advance nextBilling for all due servers
            foreach ($serverStates as &$s) {
                if ($s['nextBilling']->equalTo($minDate)) {
                    switch ($s['period']) {
                        case 'hourly': $s['nextBilling']->addHour(); break;
                        case 'daily': $s['nextBilling']->addDay(); break;
                        case 'weekly': $s['nextBilling']->addWeek(); break;
                        case 'monthly': $s['nextBilling']->addMonth(); break;
                        case 'quarterly': $s['nextBilling']->addMonths(3); break;
                        case 'half-annually': $s['nextBilling']->addMonths(6); break;
                        case 'annually': $s['nextBilling']->addYear(); break;
                    }
                }
            }
            unset($s);
            $step++;
        }
        if ($runOutDate === null && count($simulationSteps) > 0) {
            $runOutDate = Carbon::parse($simulationSteps[count($simulationSteps)-1]['date']);
        }
        return [
            'run_out_date' => $runOutDate,
            'simulation_steps' => $simulationSteps
        ];
    }

    /**
     * Format time left for display
     */
    protected function formatTimeLeft($date)
    {
        if (!$date) return null;

        $now = now();
        $daysLeft = $now->diffInDays($date, false);
        $hoursLeft = $now->diffInHours($date, false);
        $minutesLeft = $now->diffInMinutes($date, false);

        if ($daysLeft > 1) {
            return [
                'value' => floor($daysLeft),
                'unit' => 'days',
                'bg' => $daysLeft >= 15 ? self::TIME_LEFT_BG_SUCCESS :
                    ($daysLeft <= 7 ? self::TIME_LEFT_BG_DANGER : self::TIME_LEFT_BG_WARNING)
            ];
        }

        if ($hoursLeft > 1) {
            return [
                'value' => floor($hoursLeft),
                'unit' => 'hours',
                'bg' => $hoursLeft <= 24 ? self::TIME_LEFT_BG_DANGER : self::TIME_LEFT_BG_WARNING
            ];
        }

        if ($minutesLeft > 1) {
            return [
                'value' => floor($minutesLeft),
                'unit' => 'minutes',
                'bg' => self::TIME_LEFT_BG_DANGER
            ];
        }

        return [
            'value' => 'Less than 1',
            'unit' => 'minute',
            'bg' => self::TIME_LEFT_BG_DANGER
        ];
    }

    /**
     * Show the application dashboard
     */
    public function index(GeneralSettings $general_settings, WebsiteSettings $website_settings, ReferralSettings $referral_settings)
    {
        $user = Auth::user();
        $credits = $user->credits;
        $timeLeft = null;

        if ($credits > 0) {
            $cacheKey = 'user_credits_left:' . $user->id;
            $calculation = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($user, $credits) {
                return $this->calculateCreditRunout($user, $credits);
            });

            if ($calculation['run_out_date']) {
                $timeLeft = $this->formatTimeLeft($calculation['run_out_date']);
                $timeLeft['message'] = 'Estimated run out: ' . $calculation['run_out_date']->format('d.m.Y H:i');

                // For debugging
                // $timeLeft['simulation'] = $calculation['simulation_steps'];
            }
        }
        return view('home')->with([
            'usage' => $user->creditUsage(),
            'credits' => $credits,
            'useful_links_dashboard' => UsefulLink::where("position","like","%dashboard%")->get()->sortby("id"),
            'timeLeft' => $timeLeft,
            'numberOfReferrals' => DB::table('user_referrals')->where('referral_id', '=', $user->id)->count(),
            'partnerDiscount' => PartnerDiscount::where('user_id', $user->id)->first(),
            'myDiscount' => PartnerDiscount::getDiscount(),
            'general_settings' => $general_settings,
            'website_settings' => $website_settings,
            'referral_settings' => $referral_settings
        ]);
    }
}
