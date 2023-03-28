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
            <form x-data="{ payment_method: '', clicked: false }" action="{{ route('payment.pay') }}" method="POST">
                @csrf
                @method('post')
                <div class="row d-flex justify-content-center flex-wrap">
                    @if (!$productIsFree)
                        <div class="col-xl-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">
                                        <i class="fas fa-money-check-alt"></i>
                                        Payment Methods
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="payment_method" :value="payment_method"
                                        x-model="payment_method">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            @foreach ($paymentGateways as $gateway)
                                                <div class="row checkout-gateways">
                                                    <div class="col-12 d-flex justify-content-between">
                                                        <label class="form-check-label h4 checkout-gateway-label"
                                                            for="{{ $gateway->name }}">
                                                            <span class="mr-3">{{ $gateway->name }}</span>
                                                        </label>
                                                        <button class="btn btn-primary rounded" type="button"
                                                            name="payment-method" id="{{ $gateway->name }}"
                                                            value="{{ $gateway->name }}"
                                                            :class="payment_method === '{{ $gateway->name }}' ?
                                                                'active' : ''"
                                                            @click="payment_method = '{{ $gateway->name }}'; clicked = true;"
                                                            x-text="payment_method == '{{ $gateway->name }}' ? 'Selected' : 'Select'">Select</button>
                                                        </button>

                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-xl-3">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="mb-0 text-center">
                                    <i class="fas fa-shopping-cart"></i>
                                    Checkout details
                                </h4>
                            </div>

                            <div class="card-body">
                                <ul class="list-group mb-3">
                                    <li class="list-group-item">
                                        <div>
                                            <h5 class="my-0">{{ __('Product details') }}</h5>
                                        </div>
                                        <ul class="pl-0">
                                            <li class="d-flex justify-content-between">
                                                <span class="text-muted d-inline-block">{{ __('Type') }}</span>
                                                <span
                                                    class="text-muted d-inline-block">{{ strtolower($product->type) == 'credits' ? $credits_display_name : $product->type }}</span>
                                            </li>
                                            <li class="d-flex justify-content-between">
                                                <span class="text-muted d-inline-block">{{ __('Amount') }}</span>
                                                <span class="text-muted d-inline-block">{{ $product->quantity }}</span>
                                            </li>
                                            <li class="d-flex justify-content-between">
                                                <span class="text-muted d-inline-block">{{ __('Total Amount') }}</span>
                                                <span class="text-muted d-inline-block">{{ $product->quantity }}</span>
                                            </li>
                                        </ul>

                                    </li>


                                    </li>
                                    <li class="list-group-item d-flex justify-content-between lh-condensed">
                                        <div>
                                            <h6 class="my-0">{{ __('Description') }}</h6>
                                            <span class="text-muted">
                                                {{ $product->description }}
                                            </span>
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <div>
                                            <h5 class="my-0">{{ __('Pricing') }}</h5>
                                        </div>

                                        <ul class="pl-0">
                                            <li class="d-flex justify-content-between">
                                                <span class="text-muted d-inline-block">{{ __('Subtotal') }}</span>
                                                <span class="text-muted d-inline-block">
                                                    {{ $product->formatToCurrency($discountedprice) }}</span>
                                            </li>
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted d-inline-block">{{ __('Tax') }}
                                                    @if ($taxpercent > 0)
                                                        ({{ $taxpercent }}%):
                                                    @endif
                                                </span>
                                                <span class="text-muted d-inline-block">
                                                    + {{ $product->formatToCurrency($taxvalue) }}</span>
                                            </div>
                                            @if ($discountpercent && $discountvalue)
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted d-inline-block">{{ __('Discount') }}
                                                        ({{ $discountpercent }}%):</span>
                                                    <span
                                                        class="text-muted d-inline-block">-{{ $product->formatToCurrency($discountvalue) }}</span>
                                                </div>
                                            @endif
                                            <hr class="text-white border-secondary">
                                            <div class="d-flex justify-content-between">
                                                <span class="text-muted d-inline-block">{{ __('Total') }}</span>
                                                <span
                                                    class="text-muted d-inline-block">{{ $product->formatToCurrency($total) }}</span>
                                            </div>
                                            <template x-if="payment_method">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted d-inline-block">{{ __('Pay with') }}</span>
                                                    <span class="text-muted d-inline-block" x-text="payment_method"></span>
                                                </div>
                                            </template>
                                        </ul>
                                    </li>
                                </ul>

                                <button :disabled="(!payment_method || !clicked) && {{ !$productIsFree }}"
                                    :class="(!payment_method || !clicked) && {{ !$productIsFree }} ? 'disabled' : ''"
                                    class="btn btn-success float-right w-100">
                                    <i class="far fa-credit-card mr-2" @click="clicked == true"></i>
                                    @if ($productIsFree)
                                        {{ __('Get for free') }}
                                    @else
                                        {{ __('Submit Payment') }}
                                    @endif

                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </section>
    <!-- END CONTENT -->
@endsection
