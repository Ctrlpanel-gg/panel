@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-primary-950 p-8">
    <!-- Header -->
    <div class="max-w-screen-2xl mx-auto mb-8">
        <div class="glass-panel p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-light text-white">{{ __('Activity Logs') }}</h1>
                    <nav class="flex mt-2 text-sm" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 text-zinc-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">{{ __('Dashboard') }}</a></li>
                            <li class="text-zinc-600">/</li>
                            <li class="text-zinc-500">{{ __('Activity Logs') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto">
        <div class="grid grid-cols-1 gap-6">
            <!-- Cron Status -->
            <div class="glass-panel p-6">
                @if($cronlogs)
                    <div class="text-emerald-500">
                        <h4 class="text-lg font-medium">{{$cronlogs}}</h4>
                    </div>
                @else
                    <div class="text-red-500">
                        <h4 class="text-lg font-medium">{{ __('No recent activity from cronjobs')}}</h4>
                        <p class="mt-2">{{ __('Are cronjobs running?')}} <a class="text-primary hover:text-primary/80" target="_blank" href="https://CtrlPanel.gg/docs/Installation/getting-started#crontab-configuration">{{ __('Check the docs for it here')}}</a></p>
                    </div>
                @endif
            </div>

            <!-- Logs Table -->
            <div class="glass-panel">
                <div class="p-6 border-b border-zinc-800/50">
                    <div class="flex justify-between items-center">
                        <h5 class="text-lg font-medium text-white flex items-center">
                            <i class="fas fa-history mr-2 text-zinc-400"></i>
                            {{ __('Activity Logs')}}
                        </h5>
                        <div class="w-64">
                            <form method="get" action="{{route('admin.activitylogs.index')}}">
                                @csrf
                                <div class="relative">
                                    <input type="text" class="input" name="search" placeholder="Search">
                                    <button class="absolute right-0 top-0 h-full px-3" type="submit">
                                        <i class="fa fa-search text-zinc-400"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-48">{{ __('Causer') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th class="w-40">{{ __('Created at') }}</th>
                                    <th class="w-20">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr class="group hover:bg-zinc-800/30 transition-colors cursor-pointer" onclick="toggleDetails('details-{{$log->id}}')">
                                    <td class="select-none">
                                        @if($log->causer)
                                            <a href='/admin/users/{{$log->causer_id}}' class="text-primary-400 hover:text-primary-300" onclick="event.stopPropagation()">
                                                {{json_decode($log->causer)->name}}
                                            </a>
                                        @else
                                            <span class="text-zinc-500">System</span>
                                        @endif
                                    </td>
                                    <td class="relative select-none">
                                        <div class="flex flex-col gap-2">
                                            <div class="flex items-center gap-3">
                                                <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-lg 
                                                    @if (str_starts_with($log->description, 'created')) bg-emerald-500/10
                                                    @elseif(str_starts_with($log->description, 'redeemed')) bg-emerald-500/10
                                                    @elseif(str_starts_with($log->description, 'deleted')) bg-red-500/10
                                                    @elseif(str_starts_with($log->description, 'gained')) bg-emerald-500/10
                                                    @elseif(str_starts_with($log->description, 'updated')) bg-blue-500/10
                                                    @endif">
                                                    @if (str_starts_with($log->description, 'created'))
                                                        <i class="fas fa-plus text-emerald-500"></i>
                                                    @elseif(str_starts_with($log->description, 'redeemed'))
                                                        <i class="fas fa-money-check-alt text-emerald-500"></i>
                                                    @elseif(str_starts_with($log->description, 'deleted'))
                                                        <i class="fas fa-times text-red-500"></i>
                                                    @elseif(str_starts_with($log->description, 'gained'))
                                                        <i class="fas fa-money-bill text-emerald-500"></i>
                                                    @elseif(str_starts_with($log->description, 'updated'))
                                                        <i class="fas fa-pen text-blue-500"></i>
                                                    @endif
                                                </span>
                                                <div class="flex-1">
                                                    <div class="text-zinc-100 font-medium">
                                                        {{ explode('\\', $log->subject_type)[2] }}
                                                    </div>
                                                    <div class="text-sm text-zinc-500">
                                                        {{ ucfirst($log->description) }}
                                                    </div>
                                                </div>
                                            </div>
                                            @php
                                                $props = json_decode($log->properties, true);
                                            @endphp
                                            <div id="details-{{$log->id}}" class="hidden pl-11 space-y-2" onclick="event.stopPropagation()">
                                                <div class="border-l-2 border-zinc-800 pl-3 py-1">
                                                    @if ($log->description === 'created' && isset($props['attributes']))
                                                        @foreach($props['attributes'] as $key => $value)
                                                            @if(!is_null($value) && !is_array($value))
                                                                <div class="flex items-center gap-2 text-sm">
                                                                    <span class="text-zinc-500 min-w-[120px]">{{ $key }}</span>
                                                                    <span class="text-zinc-300">{{ $value }}</span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @elseif($log->description === 'updated' && isset($props['attributes'], $props['old']))
                                                        @foreach($props['attributes'] as $key => $value)
                                                            @if(array_key_exists($key, $props['old']) && !is_null($value) && !is_array($value))
                                                                <div class="flex items-center gap-2 text-sm">
                                                                    <span class="text-zinc-500 min-w-[120px]">{{ $key }}</span>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="text-red-400">{{ $props['old'][$key] }}</span>
                                                                        <i class="fas fa-arrow-right text-zinc-600 text-xs"></i>
                                                                        <span class="text-emerald-400">{{ $value }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="select-none">{{$log->created_at->diffForHumans()}}</td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary"
                                                onclick="event.stopPropagation(); showLogDetails({{ json_encode($log) }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-zinc-500">
                            {{ __('Showing') }} {{ $logs->firstItem() }} {{ __('to') }} {{ $logs->lastItem() }} {{ __('of') }} {{ $logs->total() }} {{ __('entries') }}
                        </div>
                        <div>
                            {{ $logs->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showLogDetails(log) {
    if (!log) return;

    let detailsHtml = '';
    let props;
    
    try {
        props = typeof log.properties === 'string' ? JSON.parse(log.properties) : log.properties;
    } catch (e) {
        console.error('Error parsing log properties:', e);
        return;
    }

    if (log.description === 'created' && props.attributes) {
        detailsHtml = generateCreatedDetails(props.attributes);
    } else if (log.description === 'updated' && props.attributes && props.old) {
        detailsHtml = generateUpdatedDetails(props.attributes, props.old);
    } else if (log.description === 'deleted' && props.old) {
        detailsHtml = generateDeletedDetails(props.old);
    }

    if (!detailsHtml) {
        detailsHtml = '<div class="text-zinc-400 text-center py-4">No additional details available</div>';
    }

    Swal.fire({
        title: `${log.description.charAt(0).toUpperCase() + log.description.slice(1)} ${log.subject_type.split('\\').pop()}`,
        html: `<div class="swal2-custom-content">${detailsHtml}</div>`,
        width: '850px',
        position: 'center',
        showClass: {
            popup: 'animate-in fade-in duration-200 ease-out'
        },
        hideClass: {
            popup: 'animate-out fade-out duration-200 ease-in'
        },
        customClass: {
            container: 'swal2-custom-container',
            popup: 'glass-panel !bg-zinc-900/95',
            title: 'text-white font-medium text-lg mb-4',
            htmlContainer: 'swal2-custom-html text-zinc-300 max-h-[60vh] overflow-y-auto',
            confirmButton: 'btn btn-primary text-sm px-6',
            closeButton: 'swal2-custom-close'
        },
        buttonsStyling: false,
        background: 'rgb(24 24 27 / 0.8)',
        backdrop: `
            rgba(0, 0, 0, 0.8)
            left top
            no-repeat
        `,
        confirmButtonText: 'Close',
        showCloseButton: true
    });
}

function generateCreatedDetails(attributes) {
    return Object.entries(attributes)
        .filter(([_, value]) => value !== null)
        .map(([key, value]) => `
            <div class="flex justify-between p-2 border-b border-zinc-800/50">
                <span class="font-medium text-zinc-400">${key}:</span>
                <span class="text-white">${formatValue(value)}</span>
            </div>
        `).join('');
}

function generateUpdatedDetails(attributes, old) {
    return Object.entries(attributes)
        .filter(([key, value]) => old[key] !== undefined && value !== null)
        .map(([key, value]) => `
            <div class="flex justify-between p-2 border-b border-zinc-800/50">
                <span class="font-medium text-zinc-400">${key}:</span>
                <div class="flex items-center gap-2">
                    <span class="text-red-400">${formatValue(old[key])}</span>
                    <i class="fas fa-arrow-right text-zinc-600"></i>
                    <span class="text-emerald-400">${formatValue(value)}</span>
                </div>
            </div>
        `).join('');
}

function generateDeletedDetails(old) {
    return Object.entries(old)
        .filter(([_, value]) => value !== null)
        .map(([key, value]) => `
            <div class="flex justify-between p-2 border-b border-zinc-800/50">
                <span class="font-medium text-zinc-400">${key}:</span>
                <span class="text-red-400">${formatValue(value)}</span>
            </div>
        `).join('');
}

function formatValue(value) {
    if (typeof value === 'boolean') {
        return value ? '<i class="fas fa-check text-emerald-500"></i>' : '<i class="fas fa-times text-red-500"></i>';
    }
    if (value instanceof Date || (typeof value === 'string' && value.match(/^\d{4}-\d{2}-\d{2}/))) {
        return new Date(value).toLocaleString();
    }
    return value;
}

// Add this function for toggling details
function toggleDetails(detailsId) {
    const details = document.getElementById(detailsId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    
    if (details.classList.contains('hidden')) {
        details.classList.remove('hidden');
        details.classList.add('animate-in', 'fade-in', 'duration-200');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        details.classList.add('hidden');
        details.classList.remove('animate-in', 'fade-in');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// ...rest of existing script...
</script>

<style>
/* SweetAlert2 Custom Styles */
.swal2-custom-container {
    @apply items-center justify-center p-4;
}

.swal2-custom-html {
    @apply scrollbar-thin scrollbar-thumb-zinc-700 scrollbar-track-zinc-800/50 px-1;
}

.swal2-custom-html::-webkit-scrollbar {
    @apply w-2;
}

.swal2-custom-html::-webkit-scrollbar-track {
    @apply bg-zinc-800/50 rounded-full;
}

.swal2-custom-html::-webkit-scrollbar-thumb {
    @apply bg-zinc-700 rounded-full hover:bg-zinc-600;
}

.swal2-custom-content {
    @apply divide-y divide-zinc-800/50;
}

.swal2-custom-content > div {
    @apply py-3 first:pt-0 last:pb-0;
}

.swal2-popup {
    @apply !p-0 !max-w-4xl;
}

.swal2-title {
    @apply !p-6 !m-0 border-b border-zinc-800/50;
}

.swal2-html-container {
    @apply !p-6 !m-0;
}

.swal2-actions {
    @apply !p-6 !m-0 border-t border-zinc-800/50 justify-end;
}

.swal2-custom-close {
    @apply !text-zinc-400 hover:!text-white !right-6 !top-6;
}

/* Link styles in the popup */
.swal2-html-container a {
    @apply text-primary-400 hover:text-primary-300;
}

/* Add this to your existing styles */
.animate__faster {
    animation-duration: 0.3s !important;
}

/* Add small button variant */
.btn-sm {
    @apply px-2 py-1 text-xs;
}

/* Replace animate__faster styles with Tailwind animations */
.fade-in {
    @apply transition-opacity ease-out duration-200;
}

.fade-out {
    @apply transition-opacity ease-in duration-200;
}

.animate-in {
    animation: enter 200ms ease-out;
}

.animate-out {
    animation: exit 200ms ease-in;
}

@keyframes enter {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes exit {
    from {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0;
        transform: scale(0.95);
    }
}

/* Remove animate.css link since we're using Tailwind animations */
.btn-ghost {
    @apply hover:bg-zinc-800/50 text-zinc-400 hover:text-zinc-300;
}

.btn-xs {
    @apply px-1.5 py-1 text-xs;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
@endsection
