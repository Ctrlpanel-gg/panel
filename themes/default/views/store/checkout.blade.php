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
            <form
							id="payment_form"
              action="{{ route('payment.pay') }}"
              method="POST"
              x-data="{
                payment_method: '',
                coupon_code: '',
                clicked: false,
                setCouponCode(event) {
                  this.coupon_code = event.target.value
                }
              }"
            >
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
												<div class="col-xl-4">
													<div class="card">
														<div class="card-header">
															<h4 class="mb-0">
																Coupon Code
															</h4>
														</div>
														<div class="card-body">
															<div class="d-flex">
                                <input
                                  type="text"
                                  id="coupon_code"
                                  name="coupon_code"
                                  value="{{ old('coupon_code') }}"
                                  :value="coupon_code"
                                  class="form-control @error('coupon_code') is_invalid @enderror"
                                  placeholder="SUMMER"
                                  x-on:change.debounce="setCouponCode($event)"
                                  x-model="coupon_code"
                                />
                              <button
                                type="button"
                                id="send_coupon_code"
                                class="btn btn-success ml-3"
                                :disabled="!coupon_code.length"
                                :class="!coupon_code.length ? 'disabled' : ''"
                                :value="coupon_code"
                              >
                                {{ __('Submit') }}
                              </button>
                              </div>
                              @error('coupon_code')
                                <div class="text-danger">
                                  {{ $message }}
                                </div>
                              @enderror
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
                                            <div id="coupon_discount_details" class="d-flex justify-content-between" style="display: none !important;">
                                              <span class="text-muted d-inline-block">
                                                {{ __('Coupon Discount') }}
                                              </span>
                                              <span id="coupon_discount_value" class="text-muted d-inline-block">

                                              </span>
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
                                                <input id="total_price_input" type="hidden" value="{{ $product->getTotalPrice() }}">
                                                <span
                                                  id="total_price"
                                                  class="text-muted d-inline-block"
                                                >
                                                  {{ $product->formatToCurrency($total) }}
                                                </span>
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

                                <button :disabled="(!payment_method || !clicked || coupon_code ? true : false) && {{ !$productIsFree }}"
                                    id="submit_form_button"
                                    :class="(!payment_method || !clicked || coupon_code ? true : false) && {{ !$productIsFree }} ? 'disabled' : ''"
                                    :x-text="coupon_code"
                                    class="btn btn-success float-right w-100">

                                    <i class="far fa-credit-card mr-2" @click="clicked == true"></i>
                                    @if ($productIsFree)
                                        {{ __('Get for free') }}
                                    @else
                                        {{ __('Submit Payment') }}
                                    @endif

                                </button>
                                <script>
                                </script>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </section>
    <!-- END CONTENT -->

    <script>
      $(document).ready(function() {
        const productId = $("[name='product_id']").val()
        let hasCouponCodeValue = $('#coupon_code').val().trim() !== ''

        $('#coupon_code').on('change', function(e) {
          hasCouponCodeValue = e.target.value !== ''
        })

        function calcPriceWithCouponDiscount(couponValue, couponType) {
          let totalPrice = $('#total_price_input').val()

          if (typeof totalPrice == 'string') {
            totalPrice = parseFloat(totalPrice)
          }

          if (couponType === 'percentage') {
            totalPrice = totalPrice - (totalPrice * couponValue / 100)
            $('#coupon_discount_value').text("- " + couponValue + "%")
          } else if (couponType === 'amount') {
            totalPrice = totalPrice - couponValue
            $('#coupon_discount_value').text(totalPrice)
          }

          $('#total_price').text(totalPrice)
          $('#total_price_input').val(totalPrice)
        }

				function checkCoupon() {
					const couponCode = $('#coupon_code').val()

					$.ajax({
						url: "{{ route('admin.coupon.redeem') }}",
						method: 'POST',
						data: { couponCode: couponCode, productId: productId },
						success: function(response) {
							if (response.isValid && response.couponCode) {
                Swal.fire({
                  icon: 'success',
                  text: 'The coupon was successfully added to your purchase.',
                }).then(function(isConfirmed) {
                  calcPriceWithCouponDiscount(response.couponValue, response.couponType)
                  $('#submit_form_button').prop('disabled', false).removeClass('disabled')
                  $('#send_coupon_code').prop('disabled', true)
                  $('#coupon_discount_details').prop('disabled', false).show()
                })

							} else {
								console.log('Invalid Coupon')
							}
						},
						error: function(response) {
              const responseJson = response.responseJSON

              if (!responseJson.isValid) {
                  Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: responseJson.error,
                })
              }
						}
					})
				}

				$('#payment_form').on('submit', function(e) {
					if (hasCouponCodeValue) {
						checkCoupon()
					}
				})

        $('#send_coupon_code').click(function(e) {
          if (hasCouponCodeValue) {
						checkCoupon()
					}
        })
      })
    </script>
@endsection
