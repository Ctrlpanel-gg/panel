<?php

namespace App\Classes\Settings;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class Payments
{
    public $tabTitle = 'Payment Settings';
    public $paymentSettings;

    public function __construct()
    {
        return;
    }


    public function updatePaymentSettings(Request $request)
    {

        $values = [
            //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
            "SETTINGS::PAYMENTS:PAYPAL:SECRET" => "paypal-client-secret",
            "SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID" => "paypal-client-id",
            "SETTINGS::PAYMENTS:PAYPAL:SANDBOX_SECRET" => "paypal-sandbox-secret",
            "SETTINGS::PAYMENTS:PAYPAL:SANDBOX_CLIENT_ID" => "paypal-sandbox-id",
            "SETTINGS::PAYMENTS:STRIPE:SECRET" => "stripe-secret",
            "SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET" => "stripe-endpoint-secret",
            "SETTINGS::PAYMENTS:STRIPE:TEST_SECRET" => "stripe-test-secret",
            "SETTINGS::PAYMENTS:STRIPE:ENDPOINT_TEST_SECRET" => "stripe-endpoint-test-secret",
            "SETTINGS::PAYMENTS:STRIPE:METHODS" => "stripe-methods",
            "SETTINGS::PAYMENTS:SALES_TAX" => "salex_tax"
        ];


        foreach ($values as $key => $value) {
            $param = $request->get($value);
            if (!$param) {
                $param = "";
            }
            Settings::where('key', $key)->update(['value' => $param]);
            Cache::forget("setting" . ':' . $key);
            Session::remove("locale");
        }


        return redirect()->route('admin.settings.index')->with('success', 'Payment settings updated!');
    }

}
