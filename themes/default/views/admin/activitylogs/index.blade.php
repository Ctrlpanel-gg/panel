@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('Activity Logs')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('home')}}">{{ __('Dashboard')}}</a></li>
                        <li class="breadcrumb-item"><a class="text-muted" href="{{route('admin.activitylogs.index')}}">{{ __('Activity Logs')}}</a>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-lg-4">
                    @if($cronlogs)
                        <div class="callout callout-success">
                            <h4>{{$cronlogs}}</h4>
                        </div>
                    @else
                        <div class="callout callout-danger">
                            <h4>{{ __('No recent activity from cronjobs')}}</h4>
                            <p>{{ __('Are cronjobs running?')}} <a class="text-primary" target="_blank" href="https://CtrlPanel.gg/docs/Installation/getting-started#crontab-configuration">{{ __('Check docs')}}</a></p>
                        </div>
                    @endif
                </div>
            </div>

          <div class="card">
            <div class="card-header">
              <h5 class="card-title"><i class="fas fa-history mr-2"></i>{{ __('Activity Logs')}}</h5>
            </div>
            <div class="card-body table-responsive">

              <div class="row">
                <div class="col-lg-3 offset-lg-9 col-xl-2 offset-xl-10 col-md-6 offset-md-6">
                  <form method="get" action="{{route('admin.activitylogs.index')}}">
                    <div class="input-group mb-3">
                      <input type="text" class="form-control form-control-sm" value="{{ request()->get('search') }}" name="search" placeholder="Search">
                      <div class="input-group-append">
                        <button class="btn btn-light btn-sm" type="submit"><i class="fa fa-search"></i></button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>

              <table class="table table-sm table-striped">
                <thead>
                <tr>
                  <th>{{ __('Causer') }}</th>
                  <th>{{ __('Description') }}</th>
                  <th>{{ __('Created at') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                  <tr>
                    <td>
                      @if($log->causer)
                        <a href="{{ url('/admin/users/' . $log->causer_id) }}">
                            {{ optional($log->causer)->name ?? 'Unknown User' }}
                        </a>
                      @else
                        <span class="badge badge-secondary">System</span>
                      @endif
                    </td>
                    <td>
                        <span>
                          {{-- Icons logic --}}
                          @if (str_starts_with($log->description, 'created'))
                            <small><i class="fas text-success fa-plus mr-2"></i></small>
                          @elseif(str_starts_with($log->description, 'redeemed'))
                            <small><i class="fas text-success fa-money-check-alt mr-2"></i></small>
                          @elseif(str_starts_with($log->description, 'deleted'))
                            <small><i class="fas text-danger fa-times mr-2"></i></small>
                          @elseif(str_starts_with($log->description, 'gained'))
                            <small><i class="fas text-success fa-money-bill mr-2"></i></small>
                          @elseif(str_starts_with($log->description, 'updated'))
                            <small><i class="fas text-info fa-pen mr-2"></i></small>
                          @endif

                          {{-- Subject Type Display --}}
                          @if($log->subject_type)
                            <strong>{{ class_basename($log->subject_type) }}</strong>
                          @endif
                          
                          {{ ucfirst($log->description) }}

                          @php
                            $properties = is_array($log->properties) ? $log->properties : json_decode($log->properties, true);
                          @endphp

                          {{-- Handle Created Entries --}}
                          @if ($log->description === 'created' && isset($properties['attributes']))
                            <ul class="ml-3 small">
                                @foreach ($properties['attributes'] as $attribute => $value)
                                    @if (!is_null($value) && !is_array($value))
                                      <li>
                                        <strong>{{ ucfirst($attribute) }}:</strong>
                                        {{ in_array($attribute, ['created_at', 'updated_at']) ? \Carbon\Carbon::parse($value)->toDayDateTimeString() : $value }}
                                      </li>
                                    @endif
                                @endforeach
                            </ul>
                          @endif

                          {{-- Handle Updated Entries --}}
                          @if ($log->description === 'updated' && isset($properties['attributes'], $properties['old']))
                            <ul class="ml-3 small">
                                @foreach ($properties['attributes'] as $attribute => $newValue)
                                    @if (array_key_exists($attribute, $properties['old']) && !is_null($newValue) && !is_array($newValue))
                                      <li>
                                        <strong>{{ ucfirst($attribute) }}:</strong>
                                        @if(in_array($attribute, ['created_at', 'updated_at']))
                                            {{ \Carbon\Carbon::parse($properties['old'][$attribute])->toDayDateTimeString() }} <i class="fas fa-long-arrow-alt-right mx-1"></i> {{ \Carbon\Carbon::parse($newValue)->toDayDateTimeString() }}
                                        @else
                                            {{ $properties['old'][$attribute] }} <i class="fas fa-long-arrow-alt-right mx-1"></i> {{ $newValue }}
                                        @endif
                                      </li>
                                    @endif
                                @endforeach
                            </ul>
                          @endif

                          {{-- Handle Deleted Entries --}}
                          @if ($log->description === 'deleted' && isset($properties['old']))
                            <ul class="ml-3 small">
                                @foreach ($properties['old'] as $attribute => $value)
                                    @if (!is_null($value) && !is_array($value))
                                      <li>
                                        <strong>{{ ucfirst($attribute) }}:</strong>
                                        {{ in_array($attribute, ['created_at', 'updated_at']) ? \Carbon\Carbon::parse($value)->toDayDateTimeString() : $value }}
                                      </li>
                                    @endif
                                @endforeach
                            </ul>
                          @endif
                        </span>
                    </td>
                    <td>{{$log->created_at->diffForHumans()}}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>

              <div class="float-right mt-3">
                {!! $logs->links() !!}
              </div>

            </div>
        </div>
        </div>
    </section>
@endsection
