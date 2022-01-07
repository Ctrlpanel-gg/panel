<div class="tab-pane mt-3" id="payments">
    <form method="POST" enctype="multipart/form-data" class="mb-3"
        action="{{ route('admin.settings.update.paymentsettings') }}">
        @csrf
        @method('PATCH')

        <div class="row">
            <div class="col-md-6">
                <img class="mb-3" height="50"
                     src="{{ url('/images/paypal_logo.png') }}">
                <!-- PAYPAL -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="paypal-client-id">{{ __('Enter your PayPal Client_ID') }}</label>
                        <input x-model="paypal-client-id" id="paypal-client-id" name="paypal-client-id" type="text"
                            value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID") }}"
                            class="form-control @error('paypal-client-id') is-invalid @enderror">
                    </div>
                </div>

                <!-- PAYPAL -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="paypal-client-secret">{{ __('Your PayPal Secret-Key')}} ( https://developer.paypal.com/docs/integration/direct/rest/ ) </label>
                        <input x-model="paypal-client-secret" id="paypal-client-secret" name="paypal-client-secret" type="text"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:PAYPAL:SECRET") }}"
                               class="form-control @error('paypal-client-secret') is-invalid @enderror">
                    </div>
                </div>

                <!-- PAYPAL -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="paypal-sandbox-id">{{ __('Your PayPal SANDBOX Client-ID used for testing') }}</label>
                        <input x-model="paypal-sandbox-id" id="paypal-sandbox-id" name="paypal-sandbox-id" type="text"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:PAYPAL:SANDBOX_CLIENT_ID") }}"
                               class="form-control @error('paypal-sandbox-id') is-invalid @enderror">
                    </div>
                </div>

                <!-- PAYPAL -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="paypal-sandbox-secret">{{ __('Your PayPal SANDBOX Secret-Key used for testing ') }}</label>
                        <input x-model="paypal-sandbox-secret" id="paypal-sandbox-secret" name="paypal-sandbox-secret" type="text"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:PAYPAL:SANDBOX_SECRET") }}"
                               class="form-control @error('paypal-sandbox-secret') is-invalid @enderror">
                    </div>
                </div>
            </div>
            <div class="col-md-6">

                <img class="mb-3" height="50"
                     src="{{ url('/images/stripe_logo.png') }}">
                <!-- STRIPE -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="stripe-secret">{{ __('Your Stripe  Secret-Key')}}  ( https://dashboard.stripe.com/account/apikeys )</label>
                        <input x-model="stripe-secret" id="stripe-secret" name="stripe-secret" type="text"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:SECRET") }}"
                               class="form-control @error('stripe-secret') is-invalid @enderror">
                    </div>
                </div>
                <!-- STRIPE -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="stripe-endpoint-secret">{{ __('Enter your Stripe endpoint-secret-key') }}</label>
                        <input x-model="stripe-endpoint-secret" id="stripe-endpoint-secret" name="stripe-endpoint-secret" type="text"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET") }}"
                               class="form-control @error('stripe-endpoint-secret') is-invalid @enderror">
                    </div>
                </div>

                <!-- STRIPE -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="stripe-test-secret">{{ __('Enter your Stripe test-secret-key') }}</label>
                        <input x-model="stripe-test-secret" id="stripe-test-secret" name="stripe-test-secret" type="text"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:TEST_SECRET") }}"
                               class="form-control @error('stripe-test-secret') is-invalid @enderror">
                    </div>
                </div>

                <!-- STRIPE -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="stripe-endpoint-test-secret">{{ __('Enter your Stripe endpoint-test-secret-key') }}</label>
                        <input x-model="stripe-endpoint-test-secret" id="stripe-endpoint-test-secret" name="stripe-endpoint-test-secret" type="text"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:ENDPOINT_TEST_SECRET") }}"
                               class="form-control @error('stripe-endpoint-test-secret') is-invalid @enderror">
                    </div>
                </div>

                <!-- STRIPE -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="stripe-methods">{{ __('Comma seperated list of payment methods that are enabled')}} (https://stripe.com/docs/payments/payment-methods/integration-options)</label>
                        <input x-model="stripe-methods" id="stripe-methods" name="stripe-methods" type="text"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:METHODS") }}"
                               class="form-control @error('stripe-methods') is-invalid @enderror">
                    </div>
                </div>
            </div>



            <!-- Sorry IceToast, aber kein plan wie man das hier schön gestalten soll.... -->
        <div class="row">
            <div class="col-md-6">

                <!-- Tax -->
                <div class="form-group">
                    <div class="custom-control mb-3">
                        <label for="salex_tax">{{ __('The %-value of tax that will be added to the product price on checkout')}}</label>
                        <input x-model="salex_tax" id="salex_tax" name="salex_tax" type="number"
                               value="{{ App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:SALES_TAX") }}"
                               class="form-control @error('salex_tax') is-invalid @enderror">
                    </div>
                </div>
            </div>
        </div>
        </div>
        <button class="btn btn-primary">{{ __('Submit') }}</button>

        <!-- end -->

    </form>
</div>

