@extends('layouts.main')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="mb-2 row">
            <div class="col-sm-6">
                <h1>{{__('Create role')}}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a class="text-muted" href="{{route('admin.roles.index')}}">{{__('Roles List')}}</a></li>
                </ol>
            </div>
        </div>
    </div>
</section>

    <div class="py-4 main">

        @can('admin.roles.write')
            <div class="my-3 d-flex justify-content-end">
                <a href="{{route('admin.roles.create')}}" class="btn btn-primary"><i
                        class="fa fas fa-shield-alt pe-2"></i>{{__('Create role')}}</a>
            </div>
        @endcan

        <div class="border-0 shadow card card-body table-wrapper table-responsive">
            <h2 class="mb-4 h5">{{ __('Roles') }}</h2>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title"><i class="mr-2 fas fa-user-check"></i>{{__('Roles List')}}</h5>
                </div>
                @can('admin.roles.write')
                    <a href="{{route('admin.roles.create')}}" class="float-right btn btn-primary"><i class="fa fas fa-shield-alt pe-2"></i>{{__('Create role')}}</a>
                @endcan
            </div>
            <div class="card-body table-responsive">
                <div class="card-body table-responsive">
                    <table id="datatable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{__("ID")}}</th>
                                <th>{{__("Name")}}</th>
                                <th>{{__("User count")}}</th>
                                <th>{{__("Permissions count")}}</th>
                                <th>{{__("Power")}}</th>
                                <th>{{__("Actions")}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
    @endsection
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('#datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{config("SETTINGS::LOCALE:DATATABLES")}}.json'
                },
                processing: true,
                serverSide: true, //increases loading times too much? change back to "true" if it does
                stateSave: true,
                ajax: "{{route('admin.roles.datatable')}}",
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'users_count'
                    },
                    {
                        data: 'permissions_count'
                    },
                    {
                        data: 'power'
                    },
                    {
                        data: 'actions',
                        sortable: false
                    }
                ],
                fnDrawCallback: function(oSettings) {
                    $('[data-toggle="popover"]').popover();
                }
            });

    </script>
