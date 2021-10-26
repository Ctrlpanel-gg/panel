<?php

namespace App\Http\Controllers;

use App\Models\UsefulLink;
use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }



    /** Show the application dashboard. */
    public function index(Request $request)
    {
        $usage = 0;
        $bg="";
        $boxText="";
        $unit = "";

        foreach (Auth::user()->servers as $server){
            $usage += $server->product->price;
        }

        // START OF THE TIME-REMAINING-BOX
        if(Auth::user()->Credits() > 0.01 and $usage > 0){
            $days = number_format((Auth::user()->Credits()*30)/$usage,2,'.','');
            $hours = number_format(Auth::user()->Credits()/($usage/30/24),2,'.','');

        // DEFINE THE BACKGROUND COLOR
            if($days >= 15){
                $bg =  "success";
            }elseif ($days >= 8 && $days <= 14){
                $bg =  "warning";     
            }elseif ($days <= 7){  
               $bg =  "danger";      
        }
            // DEFINE WETHER DAYS OR HOURS REMAIN
            if($days < "1"){
                if($hours < "1"){
                    $boxText = 'You ran out of Credits ';
                    }
                    else{
                        $boxText = $hours;
                        $unit = "hours";
                    }
                }else{
                  $boxText = number_format($days,0);
                  $unit = "days";
                }
        }
        
    // RETURN ALL VALUES
        return view('home')->with([
            'useage' => $usage,
            'useful_links' => UsefulLink::all()->sortBy('id'),
            'bg' => $bg,
            'boxText' => $boxText,
            'unit' => $unit
        ]);
}



}
        

