@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{__('Admin Overview')}}</h1>
                </div>

                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{__('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted"
                                                       href="{{route('admin.overview.index')}}">{{__('Admin Overview')}}</a></li>
                    </ol>
                </div>
            </div>
        </div>
        @if(Storage::get('latestVersion') && config("app.version") < Storage::get('latestVersion'))
            <div class="alert alert-danger" role="alert">
                <b><i class="fas fa-shield-alt"></i> {{__("Version Outdated:")}}</b></br>
                {{__("You are running on")}} v{{config("app.version")}}-{{config("BRANCHNAME")}}.
                    {{__("The latest Version is")}} v{{Storage::get('latestVersion')}}</br>
                <a href="https://CtrlPanel.gg/docs/Installation/updating">{{__("Consider updating now")}}</a>
            </div>
        @endif
    </section>
    <!-- END CONTENT HEADER -->

    <!-- MAIN CONTENT -->
    <section class="content">
        <div class="container-fluid">

            <div class="mb-3 row">
                <div class="col-md-3">
                    <a href="https://CtrlPanel.gg/docs/intro" class="px-3 btn btn-dark btn-block"><i
                            class="mr-2 fas fa-link"></i> {{__('Documentation')}}</a>
                </div>
                <div class="col-md-3">
                    <a href="https://github.com/Ctrlpanel-gg/panel" class="px-3 btn btn-dark btn-block"><i
                            class="mr-2 fab fa-github"></i> {{__('Github')}}</a>
                </div>
                <div class="col-md-3">
                    <a href="https://CtrlPanel.gg/docs/Contributing/donating" class="px-3 btn btn-dark btn-block"><i
                            class="mr-2 fas fa-money-bill"></i> {{__('Support CtrlPanel')}}</a>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Servers')}}
                          <i class="mr-4 fas fa-info-circle" data-toggle="popover"
                             data-trigger="hover" data-placement="top"
                             data-html="true"
                             data-content="{{ __("This shows the total active servers and the total servers. Total active servers are all servers which are not suspended") }}"></i>
                            </span>
                            <span class="info-box-number">{{$counters['servers']->active}}/{{$counters['servers']->total}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-users"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Users')}}
                              <i class="mr-4 fas fa-info-circle" data-toggle="popover"
                                  data-trigger="hover" data-placement="top"
                                  data-html="true"
                                  data-content="{{ __("This shows the total active Users and the total Users. Total active Users are all Users which are not suspended") }}"></i>
                              </span>
                          <span class="info-box-number">{{$counters['users']->active}}/{{$counters['users']->total}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning elevation-1"><i
                                class="text-white fas fa-coins"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Total')}} {{ $credits_display_name }}</span>
                            <span class="info-box-number">{{$counters['credits']}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-money-bill"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text">{{__('Payments')}}</span>
                            <span class="info-box-number">{{$counters['payments']->total}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                    <!-- /.info-box -->
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <div class="card-title ">
                                    <span><i class="mr-2 fas fa-kiwi-bird"></i>{{__('Pterodactyl')}}</span>
                                </div>
                                <a href="{{route('admin.overview.sync')}}" class="btn btn-primary btn-sm"><i
                                        class="mr-2 fas fa-sync"></i>{{__('Sync')}}</a>
                            </div>
                        </div>
                        <div class="py-1 card-body">
                            @if ($deletedNodesPresent)
                                <div class="m-2 alert alert-danger">
                                    <h5><i class="icon fas fa-exclamation-circle"></i>{{ __('Warning!') }}</h5>
                                    <p class="mb-2">
                                        {{ __('Some nodes got deleted on pterodactyl only. Please click the sync button above.') }}
                                    </p>
                                </div>
                            @endif
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>{{__('Resources')}}</th>
                                    <th>{{__('Count')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{__('Locations')}}</td>
                                    <td>{{$counters['locations']}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Nodes')}}</td>
                                    <td>{{$nodes->count()}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Nests')}}</td>
                                    <td>{{$counters['nests']}}</td>
                                </tr>
                                <tr>
                                    <td>{{__('Eggs')}}</td>
                                    <td>{{$counters['eggs']}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <span><i class="mr-2 fas fa-sync"></i>{{__('Last updated :date', ['date' => $syncLastUpdate])}}</span>
                        </div>
                    </div>
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
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between">
                                <div class="card-title ">
                                    <span><i class="mr-2 fas fa-server"></i>{{__('CtrlPanel.gg')}}</span>
                                </div>
                            </div>
                            <div class="py-1 card-body">

                            </div>
                            <div class="card-footer">
                                <span><i class="mr-2 fas fa-info"></i>{{__("Version")}} {{config("app.version")}} - {{config("BRANCHNAME")}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
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
        <!-- END CUSTOM CONTENT -->

    </section>
    <!-- END CONTENT -->
@endsection
