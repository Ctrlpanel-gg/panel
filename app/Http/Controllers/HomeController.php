<?php

namespace App\Http\Controllers;

use App\Models\Egg;
use App\Models\Product;
use App\Models\UsefulLink;
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
}
