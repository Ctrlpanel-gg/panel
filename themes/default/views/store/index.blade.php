@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{ __('Store') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="" href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('store.index') }}">{{ __('Store') }}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="mb-3 text-right">
                <button type="button" data-toggle="modal" data-target="#redeemVoucherModal" class="btn btn-primary">
                    <i class="mr-2 fas fa-money-check-alt"></i>{{ __('Redeem code') }}
                </button>
            </div>

            @if ($isStoreEnabled && $shopProducts->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="mr-2 fa fa-coins"></i>{{ $credits_display_name }}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-responsive-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($shopProducts as $shopProduct)
                                    <tr>
                                        <td>{{ Currency::formatToCurrency($shopProduct->price, $shopProduct->currency_code) }}</td>
                                        <td>{{ strtolower($shopProduct->type) == 'credits' ? $credits_display_name : $shopProduct->type }}
                                        </td>
                                        <td>
                                            @if (strtolower($shopProduct->type) == 'credits')
                                                <i class="mr-2 fa fa-coins"></i>
                                            @elseif (strtolower($shopProduct->type) == 'server slots')
                                                <i class="mr-2 fa fa-server"></i>
                                            @endif

                                            {{ $shopProduct->display }}
                                        </td>
                                        <td><a href="{{ route('checkout', $shopProduct->id) }}"
                                                class="btn btn-info @cannot('user.shop.buy') disabled @endcannot">{{ __('Purchase') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-ban"></i>
                        @if ($shopProducts->count() == 0)
                            {{ __('There are no store shopProducts!') }}
                        @else
                            {{ __('The store is not correctly configured!') }}
                        @endif
                    </h4>
                </div>
            @endif


        </div>
    </section>
    <!-- END CONTENT -->

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
