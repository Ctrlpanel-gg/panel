@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Servers</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('servers.index')}}">Servers</a>
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

            <!-- CUSTOM CONTENT -->
            <div class="d-flex justify-content-between mb-3">
                <p>Use your servers on our <a href="{{env('PTERODACTYL_URL' , 'http://localhost')}}">pterodactyl panel</a></p>
                <a @if(Auth::user()->Servers->count() >= Auth::user()->server_limit) disabled="disabled" title="Server limit reached!" @endif href="{{route('servers.create')}}" class="btn @if(Auth::user()->Servers->count() >= Auth::user()->server_limit) disabled @endif btn-primary"><i class="fa fa-plus mr-2"></i>Create
                    Server</a>
            </div>

            <div class="row">
                @foreach($servers as $server)
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header ">
                                <div class="d-flex justify-content-between">
                                    <h5 class="card-title"><i class="fas {{$server->isSuspended() ? 'text-danger' : 'text-success'}} fa-circle mr-2"></i>{{$server->name}}</h5>
                                    <div class="card-tools">
                                        <div class="dropdown no-arrow">
                                            <a  href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-white-50"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                                <a href="{{env('PTERODACTYL_URL' , 'http://localhost')}}/server/{{$server->identifier}}"  target="__blank" class="dropdown-item text-info"><i title="manage" class="fas fa-tasks mr-2"></i><span>Manage</span></a>
                                                @if(!empty(env('PHPMYADMIN_URL')))
                                                    <a href="{{env('PHPMYADMIN_URL' , 'http://localhost')}}" class="dropdown-item text-info"  target="__blank"><i title="manage" class="fas fa-database mr-2"></i><span>Database</span></a>
                                                @endif
                                                <form method="post" onsubmit="return submitResult();" action="{{route('servers.destroy' , $server->id)}}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger"><i title="delete" class="fas fa-trash mr-2"></i><span>Delete server</span></button>
                                                </form>
                                                <div class="dropdown-divider"></div>
                                                <span class="dropdown-item"><i title="Created at" class="fas fa-sync-alt mr-2"></i><span>{{$server->created_at->isoFormat('LL')}}</span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <span class="text-muted">Server details</span>
                                <table class="table">
                                    <tr>
                                        <td>CPU</td>
                                        <td>{{$server->product->cpu}} %</td>
                                    </tr>
                                    <tr>
                                        <td>RAM</td>
                                        <td>{{$server->product->memory}} MB</td>
                                    </tr>
                                    <tr>
                                        <td>Disk</td>
                                        <td>{{$server->product->disk}} MB</td>
                                    </tr>
                                    <tr>
                                        <td>Databases</td>
                                        <td>{{$server->product->databases}} MySQL</td>
                                    </tr>
                                    <tr>
                                        <td>Backups</td>
                                        <td>{{$server->product->backups}}</td>
                                    </tr>
                                </table>
                            </div>


                            <div class="card-footer d-flex justify-content-between">
                                <a href="{{env('PTERODACTYL_URL' , 'http://localhost')}}/server/{{$server->identifier}}"  target="__blank" class="btn btn-info mx-3 w-100"><i class="fas fa-tasks mr-2"></i>Manage</a>
                                @if(!empty(env('PHPMYADMIN_URL')))
                                    <a href="{{env('PHPMYADMIN_URL' , 'http://localhost')}}" target="__blank" class="btn btn-info mx-3 w-100" ><i class="fas fa-database mr-2"></i>Database</a>
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
            <!-- END CUSTOM CONTENT -->


        </div>
    </section>
    <!-- END CONTENT -->

    <script>
        function submitResult() {
            return confirm("Are you sure you wish to delete?") !== false;
        }
    </script>
@endsection
