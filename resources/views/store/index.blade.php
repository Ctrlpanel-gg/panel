@extends('layouts.main')
<?php use App\Models\PaypalProduct; ?>

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Store</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="" href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('store.index')}}">Store</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="text-right mb-3">
                <button type="button" data-toggle="modal" data-target="#redeemVoucherModal" class="btn btn-primary">
                    <i class="fas fa-money-check-alt mr-2"></i>Redeem code
                </button>
            </div>

            @if($isPaypalSetup && $products->count() > 0)

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="fa fa-coins mr-2"></i>{{CREDITS_DISPLAY_NAME}}</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-responsive-sm">
                            <thead>
                            <tr>
                                <th>Price</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php /** @var $product PaypalProduct */?>
                            @foreach($products as $product)
                                <tr>
                                    <td>{{$product->formatCurrency()}}</td>
                                    <td>{{strtolower($product->type) == 'credits' ? CREDITS_DISPLAY_NAME : $product->type}}</td>
                                    <td><i class="fa fa-coins mr-2"></i>{{$product->display}}</td>
                                    <td><a href="{{route('checkout' , $product->id)}}" class="btn btn-info">Purchase</a>
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
                    <h4><i class="icon fa fa-ban"></i> @if($products->count() == 0) There are no store products! @else The store is not correctly configured! @endif
                    </h4>
                </div>

            @endif


        </div>
    </section>
    <!-- END CONTENT -->

@endsection
