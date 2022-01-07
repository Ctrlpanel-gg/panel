@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Store') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class=""
                                href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('store.index') }}">{{ __('Store') }}</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section x-data="serverApp()" x-init="$watch('paymentMethod', value => setPaymentRoute(value))" class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">


                    <!-- Main content -->
                    <div class="invoice p-3 mb-3">
                        <!-- title row -->
                        <div class="row">
                            <div class="col-12">
                                <h4>
                                    <i class="fas fa-globe"></i> {{ config('app.name', 'Laravel') }}
                                    <small class="float-right">{{ __('Date') }}:
                                        {{ Carbon\Carbon::now()->isoFormat('LL') }}</small>
                                </h4>
                            </div>
                            <!-- /.col -->
                        </div>

                        <!-- Table row -->
                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Quantity') }}</th>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Subtotal') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td><i class="fa fa-coins mr-2"></i>{{ $product->quantity }}
                                                {{ strtolower($product->type) == 'credits' ? CREDITS_DISPLAY_NAME : $product->type }}
                                            </td>
                                            <td>{{ $product->description }}</td>
                                            <td>{{ $product->formatToCurrency($product->price) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->

                        <div class="row">
                            <!-- accepted payments column -->
                            <div class="col-6">
                                <p class="lead">{{ __('Payment Methods') }}:</p>

                                <div>
                                    @if (\App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:PAYPAL:SECRET")|| \App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:PAYPAL:SANDBOX_SECRET"))
                                        <label class="text-center " for="paypal">
                                            <img class="mb-3" height="50"
                                                src="{{ url('/images/paypal_logo.png') }}"></br>

                                            <input x-model="paymentMethod" type="radio" id="paypal" value="paypal"
                                                name="payment_method">
                                            </input>
                                        </label>
                                    @endif
                                    @if (\App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:TEST_SECRET") || \App\Models\Settings::getValueByKey("SETTINGS::PAYMENTS:STRIPE:SECRET"))
                                        <label class="ml-5 text-center " for="stripe">
                                            <img class="mb-3" height="50"
                                                src="{{ url('/images/stripe_logo.png') }}" /></br>
                                            <input x-model="paymentMethod" type="radio" id="stripe" value="stripe"
                                                name="payment_method">
                                            </input>
                                        </label>
                                    @endif
                                </div>

                            </div>
                            <!-- /.col -->
                            <div class="col-6">
                                <p class="lead">{{ __('Amount Due') }}
                                    {{ Carbon\Carbon::now()->isoFormat('LL') }}</p>

                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th style="width:50%">{{ __('Subtotal') }}:</th>
                                            <td>{{ $product->formatToCurrency($product->price) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Tax') }} ({{ $taxpercent }}%)</th>
                                            <td>{{ $product->formatToCurrency($taxvalue) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Quantity') }}:</th>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Total') }}:</th>
                                            <td>{{ $product->formatToCurrency($total) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->

                        <!-- this row will not appear when printing -->
                        <div class="row no-print">
                            <div class="col-12">
                                <a type="button" :href="paymentRoute" :disabled="!paymentRoute"
                                    :class="!paymentRoute ? 'disabled' : ''" class="btn btn-success float-right"><i
                                        class="far fa-credit-card mr-2"></i>
                                    {{ __('Submit Payment') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div>
    </section>
    <!-- END CONTENT -->

    <script>
        function serverApp() {
            return {
                //loading
                paymentMethod: '',
                paymentRoute: '',

                setPaymentRoute(provider) {
                    switch (provider) {
                        case 'paypal':
                            this.paymentRoute = '{{ route('payment.PaypalPay', $product->id) }}';
                            break;
                        case 'stripe':
                            this.paymentRoute = '{{ route('payment.StripePay', $product->id) }}';
                            break;
                        default:
                            this.paymentRoute = '{{ route('payment.PaypalPay', $product->id) }}';
                    }
                },



            }
        }
    </script>


@endsection
