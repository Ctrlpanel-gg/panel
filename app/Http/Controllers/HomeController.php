<?php

namespace App\Http\Controllers;

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
