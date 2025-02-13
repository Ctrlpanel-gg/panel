@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Checkout') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li><a href="{{ route('store.index') }}" class="hover:text-white transition-colors">{{ __('Store') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Checkout') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto" x-data="couponForm()">
        <form id="payment_form" action="{{ route('payment.pay') }}" method="POST">
            @csrf
            @method('post')
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @if (!$productIsFree)
                    <!-- Payment Methods -->
                    <div class="xl:col-span-2">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-white font-medium flex items-center gap-2">
                                    <i class="fas fa-credit-card text-zinc-400"></i>
                                    {{ __('Payment Methods') }}
                                </h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="payment_method" :value="payment_method" x-model="payment_method">
                                
                                <!-- Payment Options -->
                                <div class="space-y-3">
                                    @foreach ($paymentGateways as $gateway)
                                        <div class="flex items-center justify-between p-4 bg-zinc-800/50 rounded-lg hover:bg-zinc-800/70 transition-colors">
                                            <label class="text-white font-medium cursor-pointer" for="{{ $gateway->name }}">
                                                {{ $gateway->name }}
                                            </label>
                                            <button type="button" 
                                                class="btn" 
                                                :class="payment_method === '{{ $gateway->name }}' ? 'btn-primary' : 'bg-zinc-700 text-zinc-300 hover:bg-zinc-600'"
                                                @click="payment_method = '{{ $gateway->name }}'; submitted = true;">
                                                <span x-text="payment_method == '{{ $gateway->name }}' ? '{{ __('Selected') }}' : '{{ __('Select') }}'"></span>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Coupon Section -->
                                @if ($isCouponsEnabled)
                                    <div class="mt-6">
                                        <h3 class="text-white font-medium mb-3">{{ __('Coupon') }}</h3>
                                        <div class="flex gap-3">
                                            <input type="text" 
                                                id="coupon_code"
                                                name="coupon_code" 
                                                class="input flex-1 @error('coupon_code') border-red-500/50 @enderror" 
                                                placeholder="{{ __('Enter your coupon here...') }}"
                                                x-on:change.debounce="setCouponCode($event)"
                                                x-model="coupon_code">
                                            <button type="button"
                                                @click="checkCoupon()"
                                                class="btn"
                                                :class="!coupon_code.length ? 'bg-zinc-800 text-zinc-500 cursor-not-allowed' : 'btn-primary'"
                                                :disabled="!coupon_code.length">
                                                {{ __('Apply') }}
                                            </button>
                                        </div>
                                        @error('coupon_code')
                                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Order Summary -->
                <div class="xl:col-span-1">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-white font-medium flex items-center gap-2">
                                <i class="fas fa-shopping-cart text-zinc-400"></i>
                                {{ __('Order Summary') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            <!-- Product Details -->
                            <div class="space-y-4">
                                <div>
                                    <h4 class="text-white font-medium mb-2">{{ __('Product Details') }}</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{ __('Type') }}</span>
                                            <span class="text-zinc-300">{{ strtolower($product->type) == 'credits' ? $credits_display_name : $product->type }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{ __('Amount') }}</span>
                                            <span class="text-zinc-300">{{ $product->quantity }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-zinc-800/50">
                                    <h4 class="text-white font-medium mb-2">{{ __('Description') }}</h4>
                                    <p class="text-sm text-zinc-400">{{ $product->description }}</p>
                                </div>

                                <!-- Pricing -->
                                <div class="pt-4 border-t border-zinc-800/50">
                                    <h4 class="text-white font-medium mb-2">{{ __('Pricing') }}</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">{{ __('Subtotal') }}</span>
                                            <span class="text-zinc-300">{{ $product->formatToCurrency($product->price) }}</span>
                                        </div>
                                        
                                        <!-- Tax -->
                                        <div class="flex justify-between">
                                            <span class="text-zinc-400">
                                                {{ __('Tax') }}
                                                @if ($taxpercent > 0)({{ $taxpercent }}%)@endif
                                            </span>
                                            <span class="text-zinc-300">+ {{ $product->formatToCurrency($taxvalue) }}</span>
                                        </div>

                                        <!-- Coupon Discount -->
                                        <div x-show="couponDiscountedValue" 
                                             class="flex justify-between"
                                             style="display: none">
                                            <span class="text-zinc-400">{{ __('Coupon Discount') }}</span>
                                            <span class="text-emerald-400" x-text="couponDiscountedValue"></span>
                                        </div>

                                        <!-- Partner Discount -->
                                        @if ($discountpercent && $discountvalue)
                                            <div class="flex justify-between">
                                                <span class="text-zinc-400">
                                                    {{ __('Partner Discount') }} ({{ $discountpercent }}%)
                                                </span>
                                                <span class="text-emerald-400">- {{ $product->formatToCurrency($discountvalue) }}</span>
                                            </div>
                                        @endif

                                        <!-- Total -->
                                        <div class="pt-2 border-t border-zinc-800/50">
                                            <div class="flex justify-between font-medium">
                                                <span class="text-zinc-400">{{ __('Total') }}</span>
                                                <span class="text-white" x-text="formatToCurrency(totalPrice)"></span>
                                            </div>
                                            <input id="total_price_input" type="hidden" x-model="totalPrice">
                                        </div>

                                        <!-- Selected Payment Method -->
                                        <template x-if="payment_method">
                                            <div class="flex justify-between">
                                                <span class="text-zinc-400">{{ __('Pay with') }}</span>
                                                <span class="text-zinc-300" x-text="payment_method"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit"
                                class="btn w-full mt-6"
                                :class="(!payment_method || !clicked || coupon_code) && {{ !$productIsFree }} ? 'bg-zinc-800 text-zinc-500 cursor-not-allowed' : 'btn-primary'"
                                :disabled="(!payment_method || !clicked || coupon_code) && {{ !$productIsFree }}"
                                @click="clicked = true">
                                <i class="far fa-credit-card mr-2"></i>
                                {{ $productIsFree ? __('Get for free') : __('Submit Payment') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
        function couponForm() {
            return {
                // Get the product id from the url
                productId: window.location.pathname.split('/').pop(),
                payment_method: '',
                coupon_code: '',
                submitted: false,
                totalPrice: {{ $total }},
                couponDiscountedValue: 0,


                setCouponCode(event) {
                    this.coupon_code = event.target.value
                },

                async checkCoupon() {
                    const response = await (fetch(
                            "{{ route('admin.coupon.redeem') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                body: JSON.stringify({
                                    couponCode: this.coupon_code,
                                    productId: this.productId
                                })
                            }
                        )
                        .then(response => response.json()).catch((error) => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: "{{ __('The coupon code you entered is invalid or cannot be applied to this product.') }}"
                            })
                        }))

                    if (response.isValid && response.couponCode) {
                        Swal.fire({
                            icon: 'success',
                            text: "{{ __('The coupon was successfully added to your purchase.') }}"

                        })

                        this.calcPriceWithCouponDiscount(response.couponValue, response
                            .couponType)

                        $('#submit_form_button').prop('disabled', false).removeClass(
                            'disabled')
                        $('#send_coupon_code').prop('disabled', true)
                        $('#coupon_discount_details').prop('disabled', false).show()

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: "{{ __('The coupon code you entered is invalid or cannot be applied to this product.') }}"
                        })
                    }
                },



                calcPriceWithCouponDiscount(couponValue, couponType) {
                    let newTotalPrice = this.totalPrice


                    console.log(couponType)
                    if (couponType === 'percentage') {
                        newTotalPrice = newTotalPrice - (newTotalPrice * couponValue / 100)
                        this.couponDiscountedValue = "- " + couponValue + "%"
                    } else if (couponType === 'amount') {

                        newTotalPrice = newTotalPrice - couponValue
                        this.couponDiscountedValue = "- " + this.formatToCurrency(couponValue)
                    }

                    // format totalPrice to currency
                    this.totalPrice = this.formatToCurrency(newTotalPrice)
                },

                formatToCurrency(amount) {
                    // get language for formatting currency - use en_US as product->formatToCurrency() uses it
                    //const lang = "{{ app()->getLocale() }}"
                    const lang = 'en-US'

                    // format totalPrice to currency
                    return amount.toLocaleString(lang, {
                        style: 'currency',
                        currency: "{{ $product->currency_code }}",
                    })
                },

            }
        }
    </script>
@endsection
