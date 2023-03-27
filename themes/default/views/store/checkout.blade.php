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
                        <li class="breadcrumb-item"><a class="" href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
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
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">

                    <form x-data="{ payment_method: '', clicked: false }" action="{{ route('payment.pay') }}" method="POST">
                        @csrf
                        @method('post')
                        <!-- Main content -->
                        <div class="invoice p-3 mb-3">
                            <!-- title row -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4>
                                        <i class="fas fa-globe"></i> {{ config('app.name', 'Laravel') }}
                                        <small class="float-right">{{ __('Date') }}:
                                            {{ Carbon\Carbon::now()->isoFormat('LL') }}</small>
                                        - Checkout
                                    </h4>
                                </div>
                                <!-- /.col -->
                            </div>

                            <!-- Table row -->
                            <div class="row mb-4 d-flex align-items-center">
                                <div class="col-4">
                                    <p class="lead">{{ __('Product') }}:</p>

                                    <div class="table-responsive">
                                        <table class="table">
                                            <tr>
                                                <th style="width:50%">{{ __('Type') }}:</th>
                                                <td>
                                                    {{ strtolower($product->type) == 'credits' ? $credits_display_name : $product->type }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Amount') }}:</th>
                                                <td>{{ $product->quantity }}</td>
                                            <tr>
                                                <th>{{ __('Description') }}:</th>
                                                <td>{{ $product->description }}</td>
                                            </tr>

                                        </table>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <p class="lead">{{ __('Amount Due') }}
                                        {{ Carbon\Carbon::now()->isoFormat('LL') }}</p>

                                    <div class="table-responsive">
                                        <table class="table">
                                            @if ($discountpercent && $discountvalue)
                                                <tr>
                                                    <th>{{ __('Discount') }} ({{ $discountpercent }}%):</th>
                                                    <td>{{ $product->formatToCurrency($discountvalue) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <th style="width:50%">{{ __('Subtotal') }}:</th>
                                                <td>{{ $product->formatToCurrency($discountedprice) }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Tax') }} ({{ $taxpercent }}%):</th>
                                                <td>{{ $product->formatToCurrency($taxvalue) }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ __('Total') }}:</th>
                                                <td>{{ $product->formatToCurrency($total) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <!-- /.col -->
                                </div>

                                <!-- accepted payments column -->
                                <div class="col-4">
                                    @if (!$productIsFree)
                                        <p class="lead">{{ __('Payment Methods') }}:</p>
                                        <div class="rounded pl-3 py-2 gateway-container">
                                            @foreach ($paymentGateways as $gateway)
                                                <div class="row ">
                                                    <div class="col-sm-10 checkout-gateway-label">
                                                        <label for="{{ $gateway->name }}">
                                                            <img height="40" src="{{ $gateway->image }}"></label>
                                                    </div>
                                                    <div class="col-sm-2 checkout-gateway-radio">
                                                        <input class="checkout-gateway-radio-input" x-model="payment_method"
                                                            type="radio" id="{{ $gateway->name }}"
                                                            value="{{ $gateway->name }}">
                                                    </div>
                                                </div>
                                            @endforeach

                                        </div>
                                    @endif
                                </div>
                            </div>
                            <!-- /.row -->

                            <!-- this row will not appear when printing -->
                            <div class="row no-print">
                                <div class="col-12">
                                    <button :disabled="(!payment_method || clicked) && {{ !$productIsFree }}"
                                        :class="(!payment_method || clicked) && {{ !$productIsFree }} ? 'disabled' : ''"
                                        class="btn btn-success float-right"><i class="far fa-credit-card mr-2"
                                            @click="clicked = true"></i>
                                        @if ($productIsFree)
                                            {{ __('Get for free') }}
                                        @else
                                            {{ __('Submit Payment') }}
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- /.invoice -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div>
    </section>
    <!-- END CONTENT -->
@endsection
