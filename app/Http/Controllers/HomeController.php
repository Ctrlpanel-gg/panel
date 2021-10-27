<?php
namespace App\Http\Controllers;

use App\Models\UsefulLink;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    const TIME_LEFT_BG_SUCCESS = "bg-success";
    const TIME_LEFT_BG_WARNING = "bg-warning";
    const TIME_LEFT_BG_DANGER  = "bg-danger";


    public function __construct()
    {
        $this->middleware('auth');
    }

/** Get the Background Color for the Days-Left-Box in HomeView */
    public function getTimeLeftBoxBackground($days){
        switch($days){
            case ($days >= 15):
                return $this::TIME_LEFT_BG_SUCCESS;
                break;
            case ($days >= 8 && $days <= 14):
                return $this::TIME_LEFT_BG_WARNING;
                break;
            case ($days <= 7):
                return $this::TIME_LEFT_BG_DANGER;
                break;
            default:
                 return $this::TIME_LEFT_BG_WARNING;
            }
        }  
        
/** Get the Text for the Days-Left-Box in HomeView */
    public function getTimeLeftBoxText($days,$hours){
            if ($days < 1)
            {
                if ($hours < 1)
                {
                  return 'You ran out of Credits ';
                }
                else
                {
                    return $hours;
                }
            }
            else
            {
                return number_format($days, 0);
            }
        }

    public function getTimeLeftUnit($days){
            switch($days){
                case ($days < 1):
                    return "hours";
                    break;
                case ($days > 1):
                    return "days";
                    break;
                default:
                    return "days";
            }
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
        if ($credits > 0.01 and $usage > 0)
        {
            $days = number_format(($credits * 30) / $usage, 2, '.', '');
            $hours = number_format($credits / ($usage / 30 / 24) , 2, '.', '');

            $bg = $this->getTimeLeftBoxBackground($days);
            $boxText = $this->getTimeLeftBoxText($days,$hours);
            $unit = $this->getTimeLeftUnit($days,$hours);

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

