@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('Store')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="" href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('store.index')}}">{{__('Store')}}</a></li>
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


                    <!-- Main content -->
                    <div class="invoice p-3 mb-3">
                        <!-- title row -->
                        <div class="row">
                            <div class="col-12">
                                <h4>
                                    <i class="fas fa-globe"></i> {{ config('app.name', 'Laravel') }}
                                    <small class="float-right">{{__('Date')}}: {{Carbon\Carbon::now()->isoFormat('LL')}}</small>
                                </h4>
                            </div>
                            <!-- /.col -->
                        </div>
                        <!-- info row -->
                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                {{__('To')}}
                                <address>
                                    <strong>{{config('app.name' , 'Laravel')}}</strong><br>
                                    {{__('Email')}}: {{env('PAYPAL_EMAIL' , env('MAIL_FROM_NAME'))}}
                                </address>
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4 invoice-col">
                                {{__('From')}}
                                <address>
                                    <strong>{{Auth::user()->name}}</strong><br>
                                    {{__('Email')}}: {{Auth::user()->email}}
                                </address>
                            </div>
                            <!-- /.col -->
                            <div class="col-sm-4 invoice-col">
                                <b>{{__('Status')}}</b><br>
                                <span class="badge badge-warning">{{__('Pending')}}</span><br>
{{--                                <b>Order ID:</b> 4F3S8J<br>--}}
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
                                        <th>{{__('Quantity')}}</th>
                                        <th>{{__('Product')}}</th>
                                        <th>{{__('Description')}}</th>
                                        <th>{{__('Subtotal')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td><i class="fa fa-coins mr-2"></i>{{$product->quantity}} {{strtolower($product->type) == 'credits' ? CREDITS_DISPLAY_NAME : $product->type}}</td>
                                        <td>{{$product->description}}</td>
                                        <td>{{$product->formatToCurrency($product->price)}}</td>
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
                                <p class="lead">{{__('Payment Methods')}}:</p>

                                <img src="https://www.paypalobjects.com/digitalassets/c/website/logo/full-text/pp_fc_hl.svg" alt="Paypal">

                                <p class="text-muted well well-sm shadow-none" style="margin-top: 10px;">
                                    {{__('By purchasing this product you agree and accept our terms of service')}}</a>
                                </p>
                            </div>
                            <!-- /.col -->
                            <div class="col-6">
                                <p class="lead">{{__('Amount due')}} {{Carbon\Carbon::now()->isoFormat('LL')}}</p>

                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th style="width:50%">{{__('Subtotal')}}:</th>
                                            <td>{{$product->formatToCurrency($product->price)}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Tax')}} ({{$taxpercent}}%)</th>
                                            <td>{{$product->formatToCurrency($taxvalue)}}</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Quantity')}}:</th>
                                            <td>1</td>
                                        </tr>
                                        <tr>
                                            <th>{{__('Total')}}:</th>
                                            <td>{{$product->formatToCurrency($total)}}</td>
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
                                <a href="{{route('payment.pay' , $product->id)}}" type="button" class="btn btn-success float-right"><i class="far fa-credit-card mr-2"></i> {{__('Submit Payment')}}
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

@endsection
