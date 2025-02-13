@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Store') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Store') }}</li>
                        </ol>
                    </nav>
                </div>
                <button type="button" data-toggle="modal" data-target="#redeemVoucherModal" 
                        class="btn btn-primary">
                    <i class="fas fa-money-check-alt mr-2"></i>{{ __('Redeem code') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        @if ($isStoreEnabled && $products->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <div class="card">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-center gap-3 mb-4">
                                <div class="stats-icon {{ strtolower($product->type) == 'credits' ? 'amber' : 'blue' }}">
                                    <i class="fas {{ strtolower($product->type) == 'credits' ? 'fa-coins' : 'fa-server' }}"></i>
                                </div>
                                <h4 class="text-lg font-medium text-white">{{ strtolower($product->type) == 'credits' ? $credits_display_name : $product->type }}</h4>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <p class="text-sm text-zinc-400">{{ $product->display }}</p>
                            </div>

                            <!-- Price Box -->
                            <div class="p-3 bg-zinc-800/50 rounded-lg mb-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-zinc-400">{{ __('Price') }}</span>
                                    <span class="text-white font-medium">{{ $product->formatToCurrency($product->price) }}</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="{{ route('checkout', $product->id) }}" 
                               class="btn w-full @cannot('user.shop.buy') bg-zinc-800 text-zinc-500 cursor-not-allowed @else btn-primary @endcannot">
                                {{ __('Purchase') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="glass-panel bg-red-500/5 text-red-400">
                <div class="flex items-center gap-3 p-6">
                    <i class="fas fa-ban"></i>
                    <h4 class="font-medium">
                        @if ($products->count() == 0)
                            {{ __('There are no store products!') }}
                        @else
                            {{ __('The store is not correctly configured!') }}
                        @endif
                    </h4>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    const getUrlParameter = (param) => {
        const queryString = window.location.search;
        const urlParams = new URLSearchParams(queryString);
        return urlParams.get(param);
    }
    const voucherCode = getUrlParameter('voucher');
    //if voucherCode not empty, open the modal and fill the input
    if (voucherCode) {
        $(function() {
            $('#redeemVoucherModal').modal('show');
            $('#redeemVoucherCode').val(voucherCode);
        });
    }
</script>
@endsection
