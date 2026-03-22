@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('Application API')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('admin.api.index')}}">{{__('Application API')}}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">
            @if (session('plain_text_api_token'))
                <div class="alert alert-warning">
                    <strong>{{ __('Copy this token now:') }}</strong>
                    <code>{{ session('plain_text_api_token') }}</code>
                    <div class="mt-1 small">{{ __('It will not be shown again after this page load.') }}</div>
                </div>
            @endif

            <div class="card">

                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h5 class="card-title"><i class="fa fa-gamepad mr-2"></i>{{__('Application API')}}</h5>
                        @can('admin.api.write')
                            <a href="{{route('admin.api.create')}}" class="btn btn-sm btn-primary"><i
                                    class="fas fa-plus mr-1"></i>{{__('Create new')}}</a>
                        @endcan
                    </div>
                </div>

                <div class="card-body table-responsive">

                    <table id="datatable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>{{__('Token')}}</th>
                            <th>{{__('Memo')}}</th>
                            <th>{{__('Scopes')}}</th>
                            <th>{{__('Last used')}}</th>
                            <th>{{__('Expires')}}</th>
                            <th>{{__('Status')}}</th>
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
            return confirm("{{__('Are you sure you wish to delete?')}}") !== false;
        }

        document.addEventListener("DOMContentLoaded", function () {
            $('#datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
                },
                processing: true,
                serverSide: true,
                stateSave: true,
                ajax: "{{route('admin.api.datatable')}}",
                order: [[ 2, "desc" ]],
                columns: [
                    {data: 'token'},
                    {data: 'memo'},
                    {data: 'abilities'},
                    {data: 'last_used'},
                    {data: 'expires_at'},
                    {data: 'status'},
                    {data: 'actions' , sortable : false},
                ],
                fnDrawCallback: function( oSettings ) {
                    $('[data-toggle="popover"]').popover();
                }
            });
        });
    </script>



@endsection
