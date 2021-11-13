<?php

namespace App\Http\Controllers;

use App\Models\Egg;
use App\Models\Product;
use App\Models\UsefulLink;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{
    const TIME_LEFT_BG_SUCCESS = "bg-success";
    const TIME_LEFT_BG_WARNING = "bg-warning";
    const TIME_LEFT_BG_DANGER = "bg-danger";
    const TIME_LEFT_OUT_OF_CREDITS_TEXT = "You ran out of Credits";

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @description Get the Background Color for the Days-Left-Box in HomeView
     *
     * @param float $daysLeft
     *
     * @return string
     */
    public function getTimeLeftBoxBackground(float $daysLeft): string
    {
        if ($daysLeft >= 15) {
            return $this::TIME_LEFT_BG_SUCCESS;
        }
        if ($daysLeft <= 7) {
            return $this::TIME_LEFT_BG_DANGER;
        }
        return $this::TIME_LEFT_BG_WARNING;
    }


    /**
     * @description Set "hours", "days" or nothing behind the remaining time
     *
     * @param float $daysLeft
     * @param float $hoursLeft
     *
     * @return string|void
     */
    public function getTimeLeftBoxUnit(float $daysLeft, float $hoursLeft)
    {
        if ($daysLeft > 1) return 'days';
        return $hoursLeft < 1 ? null : "hours";
    }

    /**
     * @description Get the Text for the Days-Left-Box in HomeView
     *
     * @param float $daysLeft
     * @param float $hoursLeft
     *
     * @return string
     */
    public function getTimeLeftBoxText(float $daysLeft, float $hoursLeft)
    {
        if ($daysLeft > 1) return strval(number_format($daysLeft, 0));
        return ($hoursLeft < 1 ? $this::TIME_LEFT_OUT_OF_CREDITS_TEXT : strval($hoursLeft));
    }

    /** Show the application dashboard. */
    public function index(Request $request)
    {
        $usage = Auth::user()->creditUsage();
        $credits = Auth::user()->Credits();
        $bg = "";
        $boxText = "";
        $unit = "";

        /** Build our Time-Left-Box */
        if ($credits > 0.01 and $usage > 0) {
            $daysLeft = number_format(($credits * 30) / $usage, 2, '.', '');
            $hoursLeft = number_format($credits / ($usage / 30 / 24), 2, '.', '');

            $bg = $this->getTimeLeftBoxBackground($daysLeft);
            $boxText = $this->getTimeLeftBoxText($daysLeft, $hoursLeft);
            $unit = $daysLeft < 1 ? ($hoursLeft < 1 ? null : "hours") : "days";
        }


        // RETURN ALL VALUES
        return view('home')->with([
            'useage' => $usage,
            'credits' => $credits,
            'useful_links' => UsefulLink::all()->sortBy('id'),
            'bg' => $bg,
            'boxText' => $boxText,
            'unit' => $unit
        ]);
    }

}

