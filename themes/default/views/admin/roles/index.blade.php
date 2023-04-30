@extends('layouts.main')

@section('content')
    <div class="main py-4">

        @can('admin.roles.write')
            <div class="d-flex justify-content-end my-3">
                <a href="{{route('admin.roles.create')}}" class="btn btn-primary"><i
                        class="fa fas fa-shield-alt pe-2"></i>{{__('Create role')}}</a>
            </div>
        @endcan

        <div class="card card-body border-0 shadow table-wrapper table-responsive">
            <h2 class="mb-4 h5">{{ __('Roles') }}</h2>

            <div class="card-body table-responsive">

                <table id="datatable" class="table table-striped">
                    <thead>
                    <tr>
                        <th>{{__("Name")}}</th>
                        <th>{{__("User count")}}</th>
                        <th>{{__("Permissions count")}}</th>
                        <th>{{__("Actions")}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
@endsection
<script>

    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{config("SETTINGS::LOCALE:DATATABLES")}}.json'
            },
            processing: true,
            serverSide: false, //increases loading times too much? change back to "true" if it does
            stateSave: true,
            ajax: "{{route('admin.roles.datatable')}}",
            columns: [
                {data: 'name'},
                {data: 'usercount'},
                {data: 'permissionscount'},
                {data: 'actions' , sortable : false},
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>

