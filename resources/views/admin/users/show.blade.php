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
                        <li class="breadcrumb-item"><a href="{{route('admin.users.index')}}">Users</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('admin.users.show' , $user->id)}}">Show</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            @if($user->discordUser)
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="small-box bg-dark">
                            <div class="d-flex justify-content-between">
                                <div class="p-3">
                                    <h3>{{$user->discordUser->username}} <sup>{{$user->discordUser->locale}}</sup> </h3>
                                    <p>{{$user->discordUser->id}}
                                    </p>
                                </div>
                                <div class="p-3"><img width="100px" height="100px" class="rounded-circle" src="{{$user->discordUser->getAvatar()}}" alt="avatar"></div>
                            </div>
                            <div class="small-box-footer">
                                <i class="fab fa-discord mr-1"></i>Discord
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-users mr-2"></i>Users</h5>
                </div>
                <div class="card-body">
                    <div class="row">

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>ID</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->id}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Role</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;"
                                             class="d-inline-block text-truncate badge {{$user->role == 'admin' ? 'badge-info' : 'badge-secondary'}}">
                                           {{$user->role}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Pterodactyl ID</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->pterodactyl_id}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Email</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->email}}
                                       </span>
                                </div>
                            </div>
                        </div>


                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Server limit</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->Servers()->count()}} / {{$user->server_limit}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Name</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->name}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Verified Email</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->email_verified_at ? 'True' : 'False'}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>{{CREDITS_DISPLAY_NAME}}</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           <i class="fas fa-coins mr-2"></i>{{$user->Credits()}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Verified Discord</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->discordUser ? 'True' : 'False'}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Usage</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                          <i class="fas fa-coins mr-2"></i>{{$user->CreditUsage()}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>IP</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->ip}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Created At</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           {{$user->created_at->diffForHumans()}}
                                       </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                        </div>

                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-4">
                                    <label>Last seen</label>
                                </div>
                                <div class="col-lg-8">
                                       <span style="max-width: 250px;" class="d-inline-block text-truncate">
                                           @if($user->last_seen) {{$user->last_seen->diffForHumans()}} @else <small
                                               class="text-muted">Null</small> @endif
                                       </span>
                                </div>
                            </div>
                        </div>



                    </div>

                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-server mr-2"></i>Servers</h5>
                </div>
                <div class="card-body table-responsive">

                    @include('admin.servers.table' , ['filter' => '?user=' . $user->id])

                </div>
            </div>


        </div>
        <!-- END CUSTOM CONTENT -->
        </div>
    </section>
    <!-- END CONTENT -->



@endsection
