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
            env('PAYPAL_SECRET') && env('PAYPAL_CLIENT_ID') ||
            env('STRIPE_SECRET') && env('STRIPE_ENDPOINT_SECRET') && env('STRIPE_METHODS')
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
