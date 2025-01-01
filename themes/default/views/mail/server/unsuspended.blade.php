@component('mail::message')
# {{__('Your servers have been unsuspended')}}

<x-mail::panel>
  @foreach ($servers as $server)
    <a href="{{ $pterodactylSettings->panel_url }}/server/{{ $server->identifier }}" target="_blank">{{ $server->name }}</a><br>
  @endforeach
</x-mail::panel>

{{ __('We appreciate your continued trust in our services. If you have any questions or need assistance, feel free to reach out to our support team.') }}

{{ __('If you have any questions please let us know.') }}<br>
{{ config('app.name') }}
@endcomponent
