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
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.users.edit' , $user->id)}}">Edit</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <form action="{{route('admin.users.update', $user->id)}}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label for="name">Username</label>
                                    <input value="{{$user->name}}" id="name" name="name" type="text"
                                           class="form-control @error('name') is-invalid @enderror" required="required">
                                    @error('name')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input value="{{$user->email}}" id="email" name="email" type="text"
                                           class="form-control @error('email') is-invalid @enderror"
                                           required="required">
                                    @error('email')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="pterodactyl_id">Pterodactyl ID</label>
                                    <input value="{{$user->pterodactyl_id}}" id="pterodactyl_id" name="pterodactyl_id"
                                           type="number"
                                           class="form-control @error('pterodactyl_id') is-invalid @enderror"
                                           required="required">
                                    @error('pterodactyl_id')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                    <div class="text-muted">
                                        This ID refers to the user account created on pterodactyl's panel. <br>
                                        <small>Only edit this if you know what you're doing :)</small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="credits">Credits</label>
                                    <input value="{{$user->credits}}" id="credits" name="credits" step="any" min="0"
                                           max="99999999"
                                           type="number" class="form-control @error('credits') is-invalid @enderror"
                                           required="required">
                                    @error('credits')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="server_limit">Server Limit</label>
                                    <input value="{{$user->server_limit}}" id="server_limit" name="server_limit" min="0"
                                           max="1000000"
                                           type="number"
                                           class="form-control @error('server_limit') is-invalid @enderror"
                                           required="required">
                                    @error('server_limit')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <div>
                                        <select id="role" name="role"
                                                class="custom-select @error('role') is-invalid @enderror"
                                                required="required">
                                            <option @if($user->role == 'admin') selected @endif class="text-danger"
                                                    value="admin">
                                                Administrator
                                            </option>
                                            <option @if($user->role == 'client') selected @endif class="text-success"
                                                    value="client">
                                                Client
                                            </option>
                                            <option @if($user->role == 'member') selected @endif class="text-secondary"
                                                    value="member">
                                                Member
                                            </option>
                                        </select>
                                    </div>
                                    @error('role')
                                    <div class="text-danger">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="col">
                                <div class="form-group"><label>New Password</label> <input
                                        class="form-control @error('new_password') is-invalid @enderror"
                                        name="new_password" type="password" placeholder="••••••">

                                    @error('new_password')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group"><label>Confirm Password</label>
                                    <input
                                        class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                        name="new_password_confirmation" type="password"
                                        placeholder="••••••">

                                    @error('new_password_confirmation')
                                    <div class="invalid-feedback">
                                        {{$message}}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </section>
    <!-- END CONTENT -->



@endsection
