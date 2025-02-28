@extends('layouts.main')

@section('content')
    <!-- CONTENT HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>{{ __('Dashboard') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a class="text-muted" href="">{{ __('Dashboard') }}</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <!-- END CONTENT HEADER -->

    @if (!file_exists(base_path() . '/install.lock') && Auth::User()->hasRole("Admin"))
        <div class="callout callout-danger">
            <h4>{{ __('The installer is not locked!') }}</h4>
            <p>
                {{ __('please create a file called "install.lock" in your dashboard Root directory. Otherwise no settings will beloaded!') }}
            </p>
            <a href="/install?step=7"><button class="btn btn-outline-danger">{{ __('or click here') }}</button></a>
        </div>
    @endif

    @if ($general_settings->alert_enabled && !empty($general_settings->alert_message))
        <div class="alert mt-4 alert-{{ $general_settings->alert_type }}" role="alert">
            {!! $general_settings->alert_message !!}
        </div>
    @endif
<!-- MAIN CONTENT -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-sm-6 col-md">
                <div class="info-box">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-server"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Servers') }}</span>
                        <span class="info-box-number">{{ Auth::user()->servers()->count() }}</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-12 col-sm-6 col-md">
                <div class="mb-3 info-box">
                    <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-coins"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">{{ $general_settings->credits_display_name }}</span>
                        <span class="info-box-number">{{ number_format(bcdiv($credits, '100', 2), 2, '.', '') }}</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix hidden-md-up"></div>

            <div class="col-12 col-sm-6 col-md">
                <div class="mb-3 info-box">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-chart-line"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">{{ $general_settings->credits_display_name }}
                            {{ __('Usage') }}</span>
                        <span class="info-box-number">{{ number_format($usage, 2, '.', '') }}
                            <sup>{{ __('per month') }}</sup></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>

            <!-- /.col -->
            @if ($credits > 1 && $usage > 0)
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="mb-3 info-box">
                        <span class="info-box-icon {{ $bg }} elevation-1">
                            <i class="fas fa-hourglass-half"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ __('Out of Credits in', ['credits' =>
                                $general_settings->credits_display_name]) }}
                            </span>
                            <span class="info-box-number">{{ $boxText }}<sup>{{ $unit }}</sup></span>
                        </div>
                    </div>
                    <!-- /.info-box -->
                </div>
            @endif
            <!-- /.col -->
        </div>
        <div class="row">
            <div class="col-md-6">
                @if ($website_settings->motd_enabled)
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="mr-2 fas fa-home"></i>
                                {{ config('app.name', 'MOTD') }} - MOTD
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            {!! $website_settings->motd_message !!}
                        </div>
                        <!-- /.card-body -->
                    </div>
                @endif

                <!-- /.card -->
                @if ($website_settings->useful_links_enabled)
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="mr-2 fas fa-link"></i>
                                {{ __('Useful Links') }}
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            @if($useful_links_dashboard->count())
                                @foreach ($useful_links_dashboard as $useful_link)
                                    <div class="alert alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <h5>
                                            <a class="alert-link text-decoration-none" target="__blank"
                                                href="{{ $useful_link->link }}">
                                                <i class="{{ $useful_link->icon }} mr-2"></i>{{ $useful_link->title }}
                                            </a>
                                        </h5>
                                        {!! $useful_link->description !!}
                                    </div>
                                @endforeach
                            @else
                                <span class="text-muted">{{ __('No useful links available') }}</span>
                            @endif
                        </div>
                        <!-- /.card-body -->
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                @if ($referral_settings->enabled)
                    <!--PartnerDiscount::getDiscount()--->
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="mr-2 fas fa-handshake"></i>
                                {{ __('Partner program') }}
                            </h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="py-0 pb-2 card-body">
                            @if (Auth::user()->can("user.referral"))
                                <div class="row justify-content-between">
                                    <div class="mt-3 col-12 col-md">
                                        <span class="badge badge-success w-100" style="font-size: 14px">
                                            <i class="mr-2 fa fa-user-check"></i>
                                            {{ __('Your referral URL') }}:
                                            <span onmouseover="hoverIn()" onmouseout="hoverOut()" onclick="onClickCopy()"
                                                id="RefLink" style="cursor: pointer;">
                                                {{ __('Click to copy') }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="mt-3 col-12 col-md">
                                        <span class="badge badge-info w-100" style="font-size: 14px">{{ __('Number of referred
                                            users:') }}
                                            {{ $numberOfReferrals }}</span>
                                    </div>
                                </div>
                                @if ($partnerDiscount)
                                    <hr style="width: 100%; height:1px; border-width:0; background-color:#6c757d; margin-bottom: 0px">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Your discount') }}</th>
                                                <th>{{ __('Discount for your new users') }}</th>
                                                <th>{{ __('Reward per registered user') }}</th>
                                                <th>{{ __('New user payment commision') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $partnerDiscount->partner_discount }}%</td>
                                                <td>{{ $partnerDiscount->registered_user_discount }}%</td>
                                                <td>{{ $referral_settings->reward }}
                                                    {{ $general_settings->credits_display_name }}</td>
                                                <td>{{ $partnerDiscount->referral_system_commission == -1 ?
                                                    $referral_settings->percentage : $partnerDiscount->referral_system_commission
                                                    }}%
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <hr style="width: 100%; height:1px; border-width:0; background-color:#6c757d; margin-top: 0px">
                                @else
                                    <hr style="width: 100%; height:1px; border-width:0; background-color:#6c757d; margin-bottom: 0px">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                @if(in_array($referral_settings->mode, ["sign-up","both"]))<th>{{ __('Reward per
                                                    registered user') }}</th> @endif
                                                @if(in_array($referral_settings->mode, ["commission","both"]))<th>{{ __('New user
                                                    payment commision') }}</th> @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                @if(in_array($referral_settings->mode, ["sign-up","both"]))<td>{{
                                                    $referral_settings->reward }} {{ $general_settings->credits_display_name }}</td>
                                                @endif
                                                @if(in_array($referral_settings->mode, ["commission","both"]))<td>{{
                                                    $referral_settings->percentage }}%</td> @endif
                                            </tr>
                                        </tbody>
                                    </table>
                                    <hr style="width: 100%; height:1px; border-width:0; background-color:#6c757d; margin-top: 0px">
                                @endif
                            @else
                                <span class="badge badge-warning">
                                    <i class="mr-2 fa fa-user-check"></i>
                                    {{ __('Make a purchase to reveal your referral-URL') }}
                                </span>
                            @endif
                        </div>
                        <!-- /.card-body -->
                    </div>
                @endif
                <!-- /.card -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="mr-2 fas fa-history"></i>
                            {{ __('Activity Logs') }}
                        </h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="py-0 pb-2 card-body">
                        <ul class="list-group list-group-flush">
                            @if(Auth::user()->actions()->count())
                                @foreach (Auth::user()->actions()->take(8)->orderBy('created_at', 'desc')->get() as $log)
                                    <li class="list-group-item d-flex justify-content-between text-muted">
                                        <span>
                                            @if (str_starts_with($log->description, 'created'))
                                                <small><i class="mr-2 fas text-success fa-plus"></i></small>
                                            @elseif(str_starts_with($log->description, 'redeemed'))
                                                <small><i class="mr-2 fas text-success fa-money-check-alt"></i></small>
                                            @elseif(str_starts_with($log->description, 'deleted'))
                                                <small><i class="mr-2 fas text-danger fa-times"></i></small>
                                            @elseif(str_starts_with($log->description, 'gained'))
                                                <small><i class="mr-2 fas text-success fa-money-bill"></i></small>
                                            @elseif(str_starts_with($log->description, 'updated'))
                                                <small><i class="mr-2 fas text-info fa-pen"></i></small>
                                            @endif
                                            {{ explode('\\', $log->subject_type)[2] }}
                                            {{ ucfirst($log->description) }}

                                            @php
                                                $properties = json_decode($log->properties, true);
                                            @endphp

                                            {{-- Handle Created Entries --}}
                                            @if ($log->description === 'created' && isset($properties['attributes']))
                                                <ul class="ml-3">
                                                    @foreach ($properties['attributes'] as $attribute => $value)
                                                        @if (!is_null($value))
                                                            <li>
                                                                <strong>{{ ucfirst($attribute) }}:</strong>
                                                                {{ $attribute === 'created_at' || $attribute === 'updated_at' ?
                                                                \Carbon\Carbon::parse($value)->toDayDateTimeString() : $value }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif

                                            {{-- Handle Updated Entries --}}
                                            @if ($log->description === 'updated' && isset($properties['attributes'], $properties['old']))
                                                <ul class="ml-3">
                                                    @foreach ($properties['attributes'] as $attribute => $newValue)
                                                        @if (array_key_exists($attribute, $properties['old']) && !is_null($newValue))
                                                            <li>
                                                                <strong>{{ ucfirst($attribute) }}:</strong>
                                                                {{ $attribute === 'created_at' || $attribute === 'updated_at' ?
                                                                \Carbon\Carbon::parse($properties['old'][$attribute])->toDayDateTimeString()
                                                                . ' → ' . \Carbon\Carbon::parse($newValue)->toDayDateTimeString()
                                                                : $properties['old'][$attribute] . ' → ' . $newValue }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif

                                            {{-- Handle Deleted Entries --}}
                                            @if ($log->description === 'deleted' && isset($properties['old']))
                                                <ul class="ml-3">
                                                    @foreach ($properties['old'] as $attribute => $value)
                                                        @if (!is_null($value))
                                                            <li>
                                                                <strong>{{ ucfirst($attribute) }}:</strong>
                                                                {{ $attribute === 'created_at' || $attribute === 'updated_at' ?
                                                                \Carbon\Carbon::parse($value)->toDayDateTimeString() : $value }}
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </span>
                                        <small>
                                            {{ $log->created_at->diffForHumans() }}
                                        </small>
                                    </li>
                                @endforeach
                            @else
                                <li class="pl-0 list-group-item text-muted">
                                    <span>
                                        {{ __('No activity logs available') }}
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <!-- /.card-body -->
                </div>
            </div>
            <!-- /.card -->
        </div>
</section>
<!-- END CONTENT -->
<script>
    var originalText = document.getElementById('RefLink').innerText;
        var link = "<?php echo route('register') . '?ref=' . Auth::user()->referral_code; ?>";
        var timeoutID;

        function hoverIn() {
            document.getElementById('RefLink').innerText = link;
            timeoutID = setTimeout(function() {
                document.getElementById('RefLink').innerText = originalText;
            }, 2000);
        }

        function hoverOut() {
            document.getElementById('RefLink').innerText = originalText;
            clearTimeout(timeoutID);
        }

        function onClickCopy() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('URL copied to clipboard') }}',
                        position: 'top-middle',
                        showConfirmButton: false,
                        background: '#343a40',
                        toast: false,
                        timer: 1000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    })
                })
            } else {
                console.log('Browser Not compatible')
            }
        }
</script>
@endsection
