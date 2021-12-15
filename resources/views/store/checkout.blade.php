@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ _('Store') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class=""
                                href="{{ route('home') }}">{{ _('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('store.index') }}">{{ _('Store') }}</a>
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
                                    <small class="float-right">{{ _('Date') }}:
                                        {{ Carbon\Carbon::now()->isoFormat('LL') }}</small>
                                </h4>
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- info row -->
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                {{ __('To') }}
                                <address>
                                    <strong>{{ config('app.name', 'Controlpanel.GG') }}</strong><br>
                                    {{ _('Email') }}: {{ env('PAYPAL_EMAIL', env('MAIL_FROM_NAME')) }}
                                </address>
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4 invoice-col">
                                {{ _('From') }}
                                <address>
                                    <strong>{{ Auth::user()->name }}</strong><br>
                                    {{ _('Email') }}: {{ Auth::user()->email }}
                                </address>
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4 invoice-col">
                                <b>{{ _('Status') }}</b><br>
                                <span class="badge badge-warning">{{ _('Pending') }}</span><br>
                                {{-- <b>Order ID:</b> 4F3S8J<br> --}}
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- /.row -->

                        <!-- Table row -->
                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ _('Quantity') }}</th>
                                            <th>{{ _('Product') }}</th>
                                            <th>{{ _('Description') }}</th>
                                            <th>{{ _('Subtotal') }}</th>
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
                                    <label class="text-center " for="paypal">
                                        <img class="mb-3" height="50"
                                            src="{{ url('/images/paypal_logo.png') }}"></br>

                                        <input x-model="paymentMethod" type="radio" id="paypal" value="paypal"
                                            name="payment_method">
                                        </input>
                                    </label>

                                    <label class="ml-5 text-center " for="stripe">
                                        <img class="mb-3" height="50"
                                            src="{{ url('/images/stripe_logo.png') }}" /></br>
                                        <input x-model="paymentMethod" type="radio" id="stripe" value="stripe"
                                            name="payment_method">
                                        </input>
                                    </label>
                                </div>

                            </div>
                            <!-- /.col -->
                            <div class="col-6">
                                <p class="lead">Amount Due {{ Carbon\Carbon::now()->isoFormat('LL') }}</p>

                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th style="width:50%">Subtotal:</th>
                                            <td>{{ $product->formatToCurrency($product->price) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tax ({{ $taxpercent }}%)</th>
                                            <td>{{ $product->formatToCurrency($taxvalue) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Quantity:</th>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <th>Total:</th>
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
                                <a type="button" :href="paymentRoute" class="btn btn-success float-right"><i
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
