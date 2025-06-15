@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10" style="height: 20px;"></div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">{{ __('Imprint') }}</div>
                    <div class="card-body prose prose-invert max-w-none">
                        @include('information.imprint-content')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
