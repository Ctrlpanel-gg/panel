<?php

namespace App\Http\Controllers;

use App\Models\CreditProduct;
use App\Models\Settings;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        $isPaymentSetup = false;

        if (
            env('APP_ENV') == 'local' ||
            Settings::getValueByKey("SETTINGS::PAYMENTS:PAYPAL:SECRET") && Settings::getValueByKey("SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID") ||
            Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:SECRET") && Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET") && Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:METHODS")
        ) $isPaymentSetup = true;

        //Required Verification for creating an server
        if (Settings::getValueByKey('SETTINGS::USER:FORCE_EMAIL_VERIFICATION', false) === 'true' && !Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('profile.index')->with('error', __("You are required to verify your email address before you can purchase credits."));
        }

        //Required Verification for creating an server
        if (Settings::getValueByKey('SETTINGS::USER:FORCE_DISCORD_VERIFICATION', false) === 'true' && !Auth::user()->discordUser) {
            return redirect()->route('profile.index')->with('error', __("You are required to link your discord account before you can purchase Credits"));
        }

        return view('store.index')->with([
            'products' => CreditProduct::where('disabled', '=', false)->orderBy('price', 'asc')->get(),
            'isPaymentSetup' => $isPaymentSetup,
        ]);
    }
}
