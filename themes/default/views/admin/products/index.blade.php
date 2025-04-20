@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{__('Products')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.products.index')}}">{{__('Products')}}</a>
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
                    <div class="d-flex justify-content-between">

                        <h5 class="card-title"><i class="mr-2 fas fa-sliders-h"></i>{{__('Products')}}</h5>
                        <a href="{{route('admin.products.create')}}" class="btn btn-sm btn-primary"><i
                                class="mr-1 fas fa-plus"></i>{{__('Create new')}}</a>
                    </div>
                </div>

                <div class="card-body table-responsive">

                    <table id="datatable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>{{__('Active')}}</th>
                            <th>{{__('Name')}}</th>
                            <th>{{__('Price')}}</th>
                            <th>{{__('Billing period')}}</th>
                            <th>{{__('Memory')}}</th>
                            <th>{{__('Cpu')}}</th>
                            <th>{{__('Swap')}}</th>
                            <th>{{__('Disk')}}</th>
                            <th>{{__('Databases')}}</th>
                            <th>{{__('Backups')}}</th>
                            <th>{{__('OOM Killer')}}</th>
                            <th>{{__('Nodes')}}</th>
                            <th>{{__('Eggs')}}</th>
                            <th>{{__('Min Credits')}}</th>
                            <th>{{__('Servers')}}</th>
                             <th>{{__('Serverlimit')}}</th>
                            <th>{{__('Created at')}}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>


        </div>
        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->

    <script>
        function submitResult() {
            return confirm("{{__('Are you sure you wish to delete?')}}") !== false;
        }

        document.addEventListener("DOMContentLoaded", function () {
            $("#datatable").DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
                },
                processing: true,
                serverSide: true,
                stateSave: true,
                order: [
                    [2, "asc"]
                ],
                ajax: "{{ route('admin.products.datatable') }}",
                columns: [
                    {data: "disabled"},
                    {data: "name"},
                    {data: "price"},
                    {data: "billing_period"},
                    {data: "memory"},
                    {data: "cpu"},
                    {data: "swap"},
                    {data: "disk"},
                    {data: "databases"},
                    {data: "backups"},
                    {data: "oom_killer"},
                    {data: "nodes", sortable: false},
                    {data: "eggs", sortable: false},
                    {data: "minimum_credits"},
                    {data: "servers", sortable: false},
                    {data: "serverlimit"},
                    {data: "created_at"},
                    {data: "actions", sortable: false}
                ],
                fnDrawCallback: function (oSettings) {
                    $("[data-toggle=\"popover\"]").popover();
                }
            });
        });
    </script>



@endsection
