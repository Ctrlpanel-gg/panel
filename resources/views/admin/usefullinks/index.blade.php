@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Products</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.products.index')}}">Products</a></li>
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
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Products</h5>
                        <a href="{{route('admin.products.create')}}" class="btn btn-sm btn-primary"><i
                                class="fas fa-plus mr-1"></i>Create new</a>
                    </div>
                </div>

                <div class="card-body table-responsive">

                    <table id="datatable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>Active</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Memory</th>
                            <th>Cpu</th>
                            <th>Swap</th>
                            <th>Disk</th>
                            <th>IO</th>
                            <th>Databases</th>
                            <th>Backups</th>
                            <th>Allocations</th>
                            <th>Servers</th>
                            <th>Created at</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
            </div>


        </div>
        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->

    <script>
        function submitResult() {
            return confirm("Are you sure you wish to delete?") !== false;
        }

        document.addEventListener("DOMContentLoaded", function () {
            $('#datatable').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{route('admin.products.datatable')}}",
                columns: [
                    {data: 'disabled'},
                    {data: 'name'},
                    {data: 'price'},
                    {data: 'memory'},
                    {data: 'cpu'},
                    {data: 'swap'},
                    {data: 'disk'},
                    {data: 'io'},
                    {data: 'databases'},
                    {data: 'backups'},
                    {data: 'allocations'},
                    {data: 'servers', sortable: false},
                    {data: 'created_at'},
                    {data: 'actions', sortable: false},
                ],
                fnDrawCallback: function( oSettings ) {
                    $('[data-toggle="popover"]').popover();
                }
            });
        });
    </script>



@endsection
