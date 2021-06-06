<?php

namespace App\Http\Controllers;

use App\Models\PaypalProduct;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreController extends Controller
{
    /** Display a listing of the resource. */
    public function index(): View|Factory|Response|Application
    {
        $isPaypalSetup = false;
        if (env('PAYPAL_SECRET') && env('PAYPAL_CLIENT_ID')) $isPaypalSetup = true;

        return view('store.index')->with([
            'products' => PaypalProduct::where('disabled' , '=' , false)->orderBy('price' , 'asc')->get(),
            'isPaypalSetup' => $isPaypalSetup
        ]);
    }
}
