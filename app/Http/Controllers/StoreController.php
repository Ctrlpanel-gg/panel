<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\PaypalProduct;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        $isPaypalSetup = false;
        if (env('PAYPAL_SECRET') && env('PAYPAL_CLIENT_ID')) $isPaypalSetup = true;
        if (env('APP_ENV', 'local') == 'local') $isPaypalSetup = true;


        //Required Verification for creating an server
        if (Configuration::getValueByKey('FORCE_EMAIL_VERIFICATION', false) === 'true' && !Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('profile.index')->with('error', "You are required to verify your email address before you can purchase credits.");
        }

        //Required Verification for creating an server
        if (Configuration::getValueByKey('FORCE_DISCORD_VERIFICATION', false) === 'true' && !Auth::user()->discordUser) {
            return redirect()->route('profile.index')->with('error', "You are required to link your discord account before you can purchase ".CREDITS_DISPLAY_NAME.".");
        }

        return view('store.index')->with([
            'products' => PaypalProduct::where('disabled', '=', false)->orderBy('price', 'asc')->get(),
            'isPaypalSetup' => $isPaypalSetup
        ]);
    }
}
