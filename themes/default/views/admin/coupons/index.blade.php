@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-light text-white">{{__('Coupons')}}</h1>
                    <div class="text-zinc-400 text-sm mt-2">{{__('Manage discount coupons')}}</div>
                </div>
                <a href="{{route('admin.coupons.create')}}" class="btn-primary">
                    <i class="mr-2 fas fa-plus"></i>
                    {{__('Create new')}}
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        <div class="glass-panel">
            <div class="overflow-x-auto">
                <table id="datatable" class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-left text-zinc-400 text-sm">
                            <th class="p-4">{{__('Status')}}</th>
                            <th class="p-4">{{__('Code')}}</th>
                            <th class="p-4">{{__('Value')}}</th>
                            <th class="p-4">{{__('Used / Max Uses')}}</th>
                            <th class="p-4">{{__('Expires')}}</th>
                            <th class="p-4">{{__('Created At')}}</th>
                            <th class="p-4">{{__('Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody class="text-zinc-300"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

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
