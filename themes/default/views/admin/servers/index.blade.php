@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{ __('Servers') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                href="{{ route('admin.servers.index') }}">{{ __('Servers') }}</a></li>
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
                        <div class="card-title ">
                            <span><i class="mr-2 fas fa-server"></i>{{ __('Servers') }}</span>
                        </div>
                        <a href="{{ route('admin.servers.sync') }}" class="btn btn-primary btn-sm"><i
                                class="mr-2 fas fa-sync"></i>{{ __('Sync') }}</a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table id="datatable" class="table table-striped">
                        <thead>
                            <tr>
                                <th width="20">{{ __('Status') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Server id') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Suspended at') }}</th>
                                <th>{{ __('Created at') }}</th>
                                <th>{{ __('Actions') }}</th>
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
@endsection

<script>
    function submitResult() {
        return confirm("{{ __('Are you sure you wish to delete?') }}") !== false;
    }

    document.addEventListener("DOMContentLoaded", function() {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{ route('admin.servers.datatable') }}{{ $filter ?? '' }}",
            order: [
                [5, "desc"]
            ],
            columns: [{
                    data: 'status',
                    name: 'servers.suspended'
                },
                {
                    data: 'name'
                },
                {
                    data: 'user',
                    name: 'user.name'
                },
                {
                    data: 'identifier'
                },
                {
                    data: 'product.name',
                },
                {
                    data: 'suspended'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'actions',
                    sortable: false
                },
            ],
            fnDrawCallback: function(oSettings) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
