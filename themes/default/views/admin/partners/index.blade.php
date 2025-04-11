@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-4 sm:p-8">
    <div class="w-full">
        <!-- Header -->
        <div class="glass-panel p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-light text-white mb-2">{{ __('Partners') }}</h1>
                    <nav class="text-zinc-400 text-sm" aria-label="Breadcrumb">
                        <ol class="list-none p-0 inline-flex">
                            <li class="flex items-center">
                                <a href="{{ route('home') }}">{{ __('Dashboard') }}</a>
                                <span class="mx-2">/</span>
                            </li>
                            <li class="flex items-center text-zinc-500">
                                {{ __('Partners') }}
                            </li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.partners.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>{{ __('Create new') }}
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full">
            <div class="glass-panel p-6">
                <div class="overflow-x-auto">
                    <table id="datatable" class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-zinc-400 bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-3">{{__('User')}}</th>
                                <th class="px-4 py-3">{{__('Partner discount')}}</th>
                                <th class="px-4 py-3">{{__('Registered user discount')}}</th>
                                <th class="px-4 py-3">{{__('Referral system commission')}}</th>
                                <th class="px-4 py-3">{{__('Created')}}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function submitResult() {
        return confirm("{{__('Are you sure you wish to delete?')}}") !== false;
    }
    document.addEventListener("DOMContentLoaded", function () {
        $('#datatable').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.3/i18n/{{ $locale_datatables }}.json'
            },
            processing: true,
            serverSide: true,
            stateSave: true,
            ajax: "{{route('admin.partners.datatable')}}",
            columns: [
                {data: 'user'},
                {data: 'partner_discount'},
                {data: 'registered_user_discount'},
                {data: 'referral_system_commission'},
                {data: 'created_at'},
                {data: 'actions', sortable: false}
            ],
            fnDrawCallback: function( oSettings ) {
                $('[data-toggle="popover"]').popover();
            }
        });
    });
</script>
@endsection