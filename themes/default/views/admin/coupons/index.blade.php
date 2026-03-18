@extends('layouts.main')

@section('content')
  <!-- CONTENT HEADER -->
  <section class="content-header">
    <div class="container-fluid">
        <div class="mb-2 row">
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
                      <i class="mr-1 fas fa-plus"></i>
                      {{__('Create new')}}
                    </a>
                </div>
            </div>

            <div class="card-body table-responsive">

                <table id="datatable" class="table table-striped">
                    <thead>
                    <tr>
                        <th>{{__('Status')}}</th>
                        <th>{{__('Code')}}</th>
                        <th>{{__('Value')}}</th>
                        <th>{{__('Used / Max Uses')}}</th>
                        <th>{{__('Expires')}}</th>
                        <th>{{__('Created At')}}</th>
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
<script>
  function submitResult() {
    return confirm("{{__('Are you sure you wish to delete?')}}") !== false;
  }

  $(document).ready(function() {
    $('#datatable').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
      },
      processing: true,
      serverSide: true,
      stateSave: true,
      ajax: "{{route('admin.coupons.datatable')}}",
      columns: [
        {data: 'status'},
        {data: 'code'},
        {data: 'value'},
        {data: 'uses', sortable: false},
        {data: 'expires_at'},
        {data: 'created_at'},
        {data: 'actions', sortable: false},
      ],
      fnDrawCallback: function( oSettings ) {
        $('[data-toggle="popover"]').popover();
      }
    });
  })
</script>
@endsection
