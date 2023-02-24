@extends('layouts.main')
@section('content')
    <div class="main py-4">
        <div class="bg-white rounded shadow p-4 my-4">
            <ul class="list-inline list-group-flush list-group-borderless mb-0">
                SETTING
            </ul>
        </div>
        <div class="card card-body border-0 shadow table-wrapper table-responsive">
            @yield('settings_content')
        </div>
    </div>
@endsection