@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Payments</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('admin.payments.index')}}">Payments</a>
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

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>Payments</h5>
                </div>
                <div class="card-body table-responsive">

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Price</th>
                                <th>Payment_ID</th>
                                <th>Payer_ID</th>
                                <th>Created at</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td>{{$payment->id}}</td>
                                    <td>{{$payment->User->name}}</td>
                                    <td>{{$payment->type}}</td>
                                    <td><i class="fa fa-coins mr-2"></i>{{$payment->amount}}</td>
                                    <td>€{{$payment->Price()}}</td>
                                    <td>{{$payment->payment_id}}</td>
                                    <td>{{$payment->payer_id}}</td>
                                    <td>{{$payment->created_at->diffForHumans()}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="float-right">
                        {!!  $payments->links() !!}
                    </div>

                </div>
            </div>

        </div>
        <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->

@endsection
