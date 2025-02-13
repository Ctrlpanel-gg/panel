@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Admin Overview') }}</h1>
            <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 text-zinc-400">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                    <li class="text-zinc-600">/</li>
                    <li class="text-zinc-500">{{ __('Admin Overview') }}</li>
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto">
        @if(Storage::get('latestVersion') && config("app.version") < Storage::get('latestVersion'))
            <div class="glass-panel p-4 mb-8 bg-red-500/10 border-red-500/20">
                <div class="flex items-center gap-2 text-red-400">
                    <i class="fas fa-shield-alt"></i>
                    <h4 class="font-medium">{{ __('Version Outdated:') }}</h4>
                </div>
                <p class="mt-2 text-red-300">
                    {{ __('You are running on') }} v{{config("app.version")}}-{{config("BRANCHNAME")}}.
                    {{ __('The latest Version is') }} v{{Storage::get('latestVersion')}}
                </p>
                <a href="https://CtrlPanel.gg/docs/Installation/updating" class="text-red-400 hover:text-red-300 mt-2 inline-block">
                    {{ __('Consider updating now') }}
                </a>
            </div>
        @endif

        <!-- Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <a href="https://CtrlPanel.gg/docs/intro" class="btn bg-zinc-800 text-zinc-300 hover:bg-zinc-700 w-full text-center">
                <i class="fas fa-link mr-2"></i>{{ __('Documentation') }}
            </a>
            <a href="https://github.com/Ctrlpanel-gg/panel" class="btn bg-zinc-800 text-zinc-300 hover:bg-zinc-700 w-full text-center">
                <i class="fab fa-github mr-2"></i>{{ __('Github') }}
            </a>
            <a href="https://CtrlPanel.gg/docs/Contributing/donating" class="btn bg-zinc-800 text-zinc-300 hover:bg-zinc-700 w-full text-center">
                <i class="fas fa-money-bill mr-2"></i>{{ __('Support CtrlPanel') }}
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="stats-card">
                <div class="stats-icon blue">
                    <i class="fas fa-server"></i>
                </div>
                <div>
                    <p class="stats-text-label">{{ __('Servers') }}</p>
                    <p class="stats-text-value">{{$counters['servers']->active}}/{{$counters['servers']->total}}</p>
                </div>
            </div>

            <div class="stats-card">
                <div class="stats-icon emerald">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="stats-text-label">{{ __('Users') }}</p>
                    <p class="stats-text-value">{{$counters['users']->active}}/{{$counters['users']->total}}</p>
                </div>
            </div>

            <div class="stats-card">
                <div class="stats-icon amber">
                    <i class="fas fa-coins"></i>
                </div>
                <div>
                    <p class="stats-text-label">{{ __('Total') }} {{ $credits_display_name }}</p>
                    <p class="stats-text-value">{{$counters['credits']}}</p>
                </div>
            </div>

            <div class="stats-card">
                <div class="stats-icon red">
                    <i class="fas fa-money-bill"></i>
                </div>
                <div>
                    <p class="stats-text-label">{{ __('Payments') }}</p>
                    <p class="stats-text-value">{{$counters['payments']->total}}</p>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Column -->
            <div class="space-y-8">
                <!-- Pterodactyl Info -->
                <div class="card">
                    <div class="card-header">
                        <div class="flex justify-between items-center">
                            <h3 class="text-white font-medium flex items-center gap-2">
                                <i class="fas fa-kiwi-bird text-zinc-400"></i>
                                {{ __('Pterodactyl') }}
                            </h3>
                            <a href="{{route('admin.overview.sync')}}" class="btn btn-primary">
                                <i class="fas fa-sync mr-2"></i>{{ __('Sync') }}
                            </a>
                        </div>
                    </div>

                    <div class="p-6">
                        @if ($deletedNodesPresent)
                            <div class="mb-4 p-4 bg-red-500/10 text-red-400 rounded-lg border border-red-500/20">
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <h4 class="font-medium">{{ __('Warning!') }}</h4>
                                </div>
                                <p>{{ __('Some nodes got deleted on pterodactyl only. Please click the sync button above.') }}</p>
                            </div>
                        @endif

                        <!-- Resource Stats Table -->
                        <table class="w-full">
                            <thead class="text-zinc-400 border-b border-zinc-800">
                                <tr>
                                    <th class="text-left py-2">{{ __('Resources') }}</th>
                                    <th class="text-left py-2">{{ __('Count') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-zinc-300">
                                <tr class="border-b border-zinc-800/50">
                                    <td class="py-2">{{ __('Locations') }}</td>
                                    <td class="py-2">{{$counters['locations']}}</td>
                                </tr>
                                <tr class="border-b border-zinc-800/50">
                                    <td class="py-2">{{ __('Nodes') }}</td>
                                    <td class="py-2">{{$nodes->count()}}</td>
                                </tr>
                                <tr class="border-b border-zinc-800/50">
                                    <td class="py-2">{{ __('Nests') }}</td>
                                    <td class="py-2">{{$counters['nests']}}</td>
                                </tr>
                                <tr>
                                    <td class="py-2">{{ __('Eggs') }}</td>
                                    <td class="py-2">{{$counters['eggs']}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-header border-t border-zinc-800/50">
                        <span class="text-zinc-400">
                            <i class="fas fa-sync mr-2"></i>
                            {{ __('Last updated :date', ['date' => $syncLastUpdate]) }}
                        </span>
                    </div>
                </div>

                <!-- Latest Tickets -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <div class="card-title ">
                                <span><i class="mr-2 fas fa-ticket-alt"></i>{{__('Latest tickets')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="py-1 card-body">
                        @if(!$tickets->count())<span style="font-size: 16px; font-weight:700">{{__('There are no tickets')}}.</span>
                        @else
                            <div class="overflow-auto">
                                <table class="table">
                                    <thead>
                                    <tr class="text-nowrap">
                                        <th>{{__('Title')}}</th>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Last updated')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                        @foreach($tickets as $ticket_id => $ticket)
                                            <tr class="text-nowrap">
                                                <td><a class="text-info"  href="{{route('admin.ticket.show', ['ticket_id' => $ticket_id])}}">#{{$ticket_id}} - {{$ticket->title}}</td>
                                                <td><a href="{{route('admin.users.show', $ticket->user_id)}}">{{$ticket->user}}</a></td>
                                                <td><span class="badge {{$ticket->statusBadgeColor}}">{{$ticket->status}}</span></td>
                                                <td>{{$ticket->last_updated}}</td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Individual Nodes -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <div class="card-title ">
                                <span><i class="mr-2 fas fa-server"></i>{{__('Individual nodes')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="py-1 card-body">
                        @if ($perPageLimit)
                            <div class="m-2 alert alert-danger">
                                <h5><i class="icon fas fa-exclamation-circle"></i>{{ __('Error!') }}</h5>
                                <p class="mb-2">
                                    {{ __('You reached the Pterodactyl perPage limit. Please make sure to set it higher than your server count.') }}<br>
                                    {{ __('You can do that in settings.') }}<br><br>
                                    {{ __('Note') }}: {{ __('If this error persists even after changing the limit, it might mean a server was deleted on Pterodactyl, but not on CtrlPanel. Try clicking the button below.') }}
                                </p>
                                <a href="{{route('admin.servers.sync')}}" class="btn btn-primary btn-md"><i
                                    class="mr-2 fas fa-sync"></i>{{__('Sync servers')}}</a>
                            </div>
                        @endif
                        <div class="overflow-auto">
                            <table class="table">
                                <thead>
                                <tr class="text-nowrap">
                                    <th>{{__('ID')}}</th>
                                    <th>{{__('Node')}}</th>
                                    <th>{{__('Server count')}}</th>
                                    <th>{{__('Resource usage')}}</th>
                                    <th>{{ $credits_display_name . ' ' . __('Usage') ." (".__('per month').")"}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($nodes as $nodeID => $node)
                                        <tr>
                                            <td>{{$nodeID}}</td>
                                            <td>{{$node->name}}</td>
                                            <td>{{$node->activeServers}}/{{$node->totalServers}}</td>
                                            <td>{{$node->usagePercent}}%</td>
                                            <td>{{$node->activeEarnings}}/{{$node->totalEarnings}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="text-nowrap" colspan="2"><span style="float: right; font-weight: 700">{{__('Total')}} ({{__('active')}}/{{__('total')}}):</span></td>
                                        <td>{{$counters['servers']->active}}/{{$counters['servers']->total}}</td>
                                        <td>{{$counters['totalUsagePercent']}}%</td>
                                        <td>{{$counters['earnings']->active}}/{{$counters['earnings']->total}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <hr style="width: 100%; height:2px; border-width:0; background-color:#6c757d; margin-top: 0px;">
                    </div>
                </div>

                <!-- Latest Payments -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <div class="card-title ">
                                <span><i class="mr-2 fas fa-file-invoice-dollar"></i>{{__('Latest payments')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="py-1 card-body">
                        <div class="row">
                            @if($counters['payments']['lastMonth']->count())
                                <div class="col-md-6" style="border-right:1px solid #6c757d">
                                    <span style="margin:auto; display:table; font-size: 18px; font-weight:700">{{__('Last month')}}:
                                        <i data-toggle="popover" data-trigger="hover" data-html="true"
                                        data-content="{{ __('Payments in this time window') }}:<br>{{$counters['payments']['lastMonth']->timeStart}} - {{$counters['payments']['lastMonth']->timeEnd}}"
                                        class="fas fa-info-circle"></i>
                                    </span>
                                    <div class="overflow-auto">
                                        <table class="table">
                                            <thead>
                                            <tr class="text-nowrap">
                                                <th><b>{{__('Currency')}}</b></th>
                                                <th>{{__('Number of payments')}}</th>
                                                <th>{{__('Total amount')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($counters['payments']['lastMonth'] as $currency => $income)
                                                    <tr>
                                                        <td>{{$currency}}</td>
                                                        <td>{{$income->count}}</td>
                                                        <td>{{$income->total}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            @if($counters['payments']['lastMonth']->count()) <div class="col-md-6">
                            @else <div class="col-md-12"> @endif
                                <span style="margin:auto; display:table; font-size: 18px; font-weight:700">{{__('This month')}}:
                                    <i data-toggle="popover" data-trigger="hover" data-html="true"
                                    data-content="{{ __('Payments in this time window') }}:<br>{{$counters['payments']['thisMonth']->timeStart}} - {{$counters['payments']['thisMonth']->timeEnd}}"
                                    class="fas fa-info-circle"></i>
                                </span>
                                <div class="overflow-auto">
                                    <table class="table">
                                        <thead>
                                        <tr class="text-nowrap">
                                            <th><b>{{__('Currency')}}</b></th>
                                            <th>{{__('Number of payments')}}</th>
                                            <th>{{__('Total amount')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($counters['payments']['thisMonth'] as $currency => $income)
                                                <tr>
                                                    <td>{{$currency}}</td>
                                                    <td>{{$income->count}}</td>
                                                    <td>{{$income->total}}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Tax Overview -->
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <div class="card-title ">
                                <span><i class="mr-2 fas fa-hand-holding-usd"></i>{{__('Tax overview')}}</span>
                            </div>
                        </div>
                    </div>
                    <div class="py-1 card-body">
                        @if($counters['taxPayments']['lastYear']->count())
                            <span style="margin:auto; display:table; font-size: 18px; font-weight:700">{{__('Last year')}}:
                                <i data-toggle="popover" data-trigger="hover" data-html="true"
                                data-content="{{ __('Payments in this time window') }}:<br>{{$counters['taxPayments']['lastYear']->timeStart}} - {{$counters['taxPayments']['lastYear']->timeEnd}}"
                                class="fas fa-info-circle"></i>
                            </span>
                            <div class="overflow-auto">
                                <table class="table">
                                    <thead>
                                    <tr class="text-nowrap">
                                        <th><b>{{__('Currency')}}</b></th>
                                        <th>{{__('Number of payments')}}</th>
                                        <th><b>{{__('Base amount')}}</b></th>
                                        <th><b>{{__('Total taxes')}}</b></th>
                                        <th>{{__('Total amount')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($counters['taxPayments']['lastYear'] as $currency => $income)
                                            <tr>
                                                <td>{{$currency}}</td>
                                                <td>{{$income->count}}</td>
                                                <td>{{$income->price}}</td>
                                                <td>{{$income->taxes}}</td>
                                                <td>{{$income->total}}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <hr style="width: 100%; height:2px; border-width:0; background-color:#6c757d; margin-top: 0px; margin-bottom: 8px">
                        @endif
                        <span style="margin:auto; display:table; font-size: 18px; font-weight:700">{{__('This year')}}:
                            <i data-toggle="popover" data-trigger="hover" data-html="true"
                            data-content="{{ __('Payments in this time window') }}:<br>{{$counters['taxPayments']['thisYear']->timeStart}} - {{$counters['taxPayments']['thisYear']->timeEnd}}"
                            class="fas fa-info-circle"></i>
                        </span>
                        <div class="overflow-auto">
                            <table class="table">
                                <thead>
                                <tr class="text-nowrap">
                                    <th><b>{{__('Currency')}}</b></th>
                                    <th>{{__('Number of payments')}}</th>
                                    <th><b>{{__('Base amount')}}</b></th>
                                    <th><b>{{__('Total taxes')}}</b></th>
                                    <th>{{__('Total amount')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($counters['taxPayments']['thisYear'] as $currency => $income)
                                        <tr>
                                            <td>{{$currency}}</td>
                                            <td>{{$income->count}}</td>
                                            <td>{{$income->price}}</td>
                                            <td>{{$income->taxes}}</td>
                                            <td>{{$income->total}}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <hr style="width: 100%; height:2px; border-width:0; background-color:#6c757d; margin-top: 0px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
