@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <header class="max-w-screen-xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <h1 class="text-3xl font-light text-white">{{ __('Activity Logs') }}</h1>
            <div class="text-zinc-400 text-sm mt-2">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a>
                <span class="px-2">›</span>
                <span>{{ __('Activity Logs') }}</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-screen-xl mx-auto space-y-8">
        <!-- Cron Status -->
        <div class="glass-panel {{ $cronlogs ? 'bg-emerald-500/5 border-emerald-500/20' : 'bg-red-500/5 border-red-500/20' }} p-6">
            @if($cronlogs)
                <h4 class="text-emerald-400 font-medium mb-1">{{ $cronlogs }}</h4>
            @else
                <div class="flex flex-col">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-exclamation-circle text-lg text-red-400"></i>
                        <h4 class="text-red-400 font-medium">{{ __('No recent activity from cronjobs') }}</h4>
                    </div>
                    <p class="text-red-400/80">
                        {{ __('Are cronjobs running?') }}
                        <a class="text-red-400 underline hover:no-underline" target="_blank" href="https://CtrlPanel.gg/docs/Installation/getting-started#crontab-configuration">
                            {{ __('Check the docs for it here') }}
                        </a>
                    </p>
                </div>
            @endif
        </div>

        <!-- Logs Table -->
        <div class="card glass-morphism">
            <div class="p-6 border-b border-zinc-800/50">
                <div class="flex justify-between items-center">
                    <h3 class="text-white font-medium flex items-center gap-2">
                        <i class="fas fa-history text-zinc-400"></i>
                        {{ __('Activity Logs') }}
                    </h3>
                    
                    <form method="get" action="{{ route('admin.activitylogs.index') }}" class="flex gap-2">
                        @csrf
                        <input type="text" 
                               name="search" 
                               placeholder="Search" 
                               class="bg-zinc-800/50 border-zinc-700/50 text-zinc-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-64">
                        <button type="submit" class="px-3 py-2 bg-zinc-800/50 text-zinc-400 rounded-lg hover:bg-zinc-800 transition-colors">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="p-6">
                <div class="space-y-4">
                    @foreach($logs as $log)
                        <div class="flex justify-between items-start p-4 bg-zinc-800/50 rounded-lg">
                            <div class="space-y-2">
                                <div class="flex items-center gap-2">
                                    @if($log->causer)
                                        <a href="/admin/users/{{$log->causer_id}}" class="text-blue-400 hover:text-blue-300">
                                            {{json_decode($log->causer)->name}}
                                        </a>
                                    @else
                                        <span class="text-zinc-400">System</span>
                                    @endif
                                </div>
                                
                                <div class="text-zinc-300">
                                    @if (str_starts_with($log->description, 'created'))
                                        <i class="fas fa-plus text-emerald-500 mr-2"></i>
                                    @elseif(str_starts_with($log->description, 'redeemed'))
                                        <i class="fas fa-money-check-alt text-emerald-500 mr-2"></i>
                                    @elseif(str_starts_with($log->description, 'deleted'))
                                        <i class="fas fa-times text-red-500 mr-2"></i>
                                    @elseif(str_starts_with($log->description, 'gained'))
                                        <i class="fas fa-money-bill text-emerald-500 mr-2"></i>
                                    @elseif(str_starts_with($log->description, 'updated'))
                                        <i class="fas fa-pen text-blue-500 mr-2"></i>
                                    @endif
                                    
                                    {{ explode('\\', $log->subject_type)[2] }}
                                    {{ ucfirst($log->description) }}

                                    @php
                                        $properties = json_decode($log->properties, true);
                                    @endphp

                                    <div class="mt-2 ml-6 text-sm space-y-1">
                                        @if ($log->description === 'created' && isset($properties['attributes']))
                                            @foreach ($properties['attributes'] as $attribute => $value)
                                                @if (!is_null($value))
                                                    <div class="text-zinc-400">
                                                        <span class="text-zinc-500">{{ ucfirst($attribute) }}:</span>
                                                        {{ $attribute === 'created_at' || $attribute === 'updated_at' ? \Carbon\Carbon::parse($value)->toDayDateTimeString() : $value }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if ($log->description === 'updated' && isset($properties['attributes'], $properties['old']))
                                            @foreach ($properties['attributes'] as $attribute => $newValue)
                                                @if (array_key_exists($attribute, $properties['old']) && !is_null($newValue))
                                                    <div class="text-zinc-400">
                                                        <span class="text-zinc-500">{{ ucfirst($attribute) }}:</span>
                                                        {{ $attribute === 'created_at' || $attribute === 'updated_at' ? 
                                                            \Carbon\Carbon::parse($properties['old'][$attribute])->toDayDateTimeString() . ' → ' . \Carbon\Carbon::parse($newValue)->toDayDateTimeString() 
                                                            : $properties['old'][$attribute] . ' → ' . $newValue }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if ($log->description === 'deleted' && isset($properties['old']))
                                            @foreach ($properties['old'] as $attribute => $value)
                                                @if (!is_null($value))
                                                    <div class="text-zinc-400">
                                                        <span class="text-zinc-500">{{ ucfirst($attribute) }}:</span>
                                                        {{ $attribute === 'created_at' || $attribute === 'updated_at' ? \Carbon\Carbon::parse($value)->toDayDateTimeString() : $value }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-zinc-600 text-sm">
                                {{$log->created_at->diffForHumans()}}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {!! $logs->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
