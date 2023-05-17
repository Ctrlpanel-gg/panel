@extends('layouts.main')

@section('content')
  <!-- CONTENT HEADER -->
  <section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{__('Coupons')}}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                    <li class="breadcrumb-item"><a class="text-muted"
                                                   href="{{route('admin.coupons.index')}}">{{__('Coupons')}}</a></li>
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
                    <h5 class="card-title">
                      <i class="nav-icon fas fa-ticket-alt"></i>
                      {{__('Coupons')}}
                    </h5>
                    <a href="{{route('admin.coupons.create')}}" class="btn btn-sm btn-primary">
                      <i class="fas fa-plus mr-1"></i>
                      {{__('Create new')}}
                    </a>
                </div>
            </div>

            <div class="card-body table-responsive">

                <table id="datatable" class="table table-striped">
                    <thead>
                    <tr>
                        <th>{{__('Partner discount')}}</th>
                        <th>{{__('Registered user discount')}}</th>
                        <th>{{__('Referral system commission')}}</th>
                        <th>{{__('Created')}}</th>
                        <th>{{__('Actions')}}</th>
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
@endsection
