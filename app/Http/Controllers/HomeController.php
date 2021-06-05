<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        //set cookie as extra layer of defense against users that make multiple accounts
        setcookie('4b3403665fea6' , base64_encode(1) , time() + (20 * 365 * 24 * 60 * 60));

        $useage = 0;

        foreach (Auth::user()->Servers as $server){
            $useage += $server->product->price;
        }

        return view('home')->with([
            'useage' => $useage
        ]);
    }
}
