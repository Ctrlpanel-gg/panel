<?php

namespace App\Http\Controllers;

use App\Models\UsefulLink;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Show the application dashboard. */
    public function index(Request $request)
    {
        //set cookie as extra layer of defense against users that make multiple accounts
        setcookie('4b3403665fea6' , base64_encode(1) , time() + (20 * 365 * 24 * 60 * 60));

        $usage = 0;

        foreach (Auth::user()->Servers as $server){
            $usage += $server->product->price;
        }

        $useful_links = DB::table('useful_links')->get();

        return view('home')->with([
            'useage' => $usage,
            'useful_links' => $useful_links
        ]);
    }
}
