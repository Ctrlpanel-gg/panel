@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Payments') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('admin.payments.index') }}">{{ __('Payments') }}</a>
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
                    <h5 class="card-title"><i class="fas fa-money-bill-wave mr-2"></i>{{ __('Payments') }}</h5>
                </div>

                <div class="card-body table-responsive">
                    <table id="datatable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Product Price') }}</th>
                                <th>{{ __('Tax Value') }}</th>
                                <th>{{ __('Tax Percentage') }}</th>
                                <th>{{ __('Total Price') }}</th>
                                <th>{{ __('Payment ID') }}</th>
                                <th>{{ __('Payment Method') }}</th>
                                <th>{{ __('Created at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{ route('admin.payments.datatable') }}",
                columns: [
                    {data: 'id',name: 'payments.id'},
                    {data: 'type'},
                    {data: 'amount'},
                    {data: 'price'},
                    {data: 'tax_value'},
                    {data: 'tax_percent'},
                    {data: 'total_price'},
                    {data: 'payment_id'},
                    {data: 'payment_method'},
                    {data: 'created_at'},
                ],
                fnDrawCallback: function(oSettings) {
                    $('[data-toggle="popover"]').popover();
                },
            });
        });
    </script>

@endsection
