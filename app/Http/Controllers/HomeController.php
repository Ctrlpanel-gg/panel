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

        foreach (Auth::user()->servers as $server){
            $usage += $server->product->price;
        }

        return view('home')->with([
            'useage' => $usage,
            'useful_links' => UsefulLink::all()->sortBy('id')
        ]);
    }

    public static  function CreditsLeftBox(){
        $usage = 0;

        foreach (Auth::user()->servers as $server){
            $usage += $server->product->price;
        }
        
        if(Auth::user()->Credits() > 0.01 and $usage > 0){
            $days = number_format((Auth::user()->Credits()*30)/$usage,2,'.','');
            $hours = number_format(Auth::user()->Credits()/($usage/30/24),2,'.','');
                echo '
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="info-box mb-3">';
                            if($days >= 15){
                                echo '<span class="info-box-icon bg-success elevation-1">';
                            }
                            elseif ($days >= 8 && $days <= 14){
                                echo '<span class="info-box-icon bg-warning elevation-1">';
                            }
                            elseif ($days <= 7){
                                echo '<span class="info-box-icon bg-danger elevation-1">';
                            }
                            
                                echo '<i class="fas fa-hourglass-half"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Out of '. Configuration::getValueByKey('CREDITS_DISPLAY_NAME').' in </span>';
                                //IF TIME IS LESS THAN 1 DAY CHANGE TO "hours"
                                if($days < "1"){
                                    if($hours < "1"){
                                        echo '<span class="info-box-number">You ran out of Credits </span>';
                                    }
                                    else{
                                        echo '<span class="info-box-number"> '.$hours.' <sup> hours</sup></span>';
                                    }
                                }else{
                                   echo '<span class="info-box-number">'.number_format($days,0).' <sup> days</sup></span>';
                                }
                            }
                            echo'
                            </div>
                        </div>';                  
        }
}
