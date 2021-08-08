@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Users</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('admin.users.index')}}">Users</a></li>
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
                        <h5 class="card-title"><i class="fas fa-users mr-2"></i>Users</h5>
                        <a href="{{route('admin.users.notifications')}}" class="btn btn-sm btn-primary"><i
                                class="fas fa-paper-plane mr-1"></i>Notify</a>
                    </div>
                </div>

                <div class="card-body table-responsive">

                    <table id="datatable" class="table table-striped">
                        <thead>
                        <tr>
                            <th>discordId</th>
                            <th>ip</th>
                            <th>pterodactyl_id</th>
                            <th>Avatar</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>{{CREDITS_DISPLAY_NAME}}</th>
                            <th>Usage</th>
                            <th>Servers</th>
                            <th>Verified</th>
                            <th>Last seen</th>
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
                ajax: "{{route('admin.users.datatable')}}",
                order: [[ 11, "desc" ]],
                columns: [
                    {data: 'discordId', visible: false, name: 'discordUser.id'},
                    {data: 'pterodactyl_id', visible: false},
                    {data: 'ip', visible: false},
                    {data: 'avatar' , sortable : false},
                    {data: 'name'},
                    {data: 'role'},
                    {data: 'email', name: 'users.email'},
                    {data: 'credits' , name : 'users.credits'},
                    {data: 'usage' , sortable : false},
                    {data: 'servers' , sortable : false},
                    {data: 'verified' , sortable : false},
                    {data: 'last_seen'},
                    {data: 'actions' , sortable : false},
                ],
                fnDrawCallback: function( oSettings ) {
                    $('[data-toggle="popover"]').popover();
                }
            });
        });
    </script>



@endsection
