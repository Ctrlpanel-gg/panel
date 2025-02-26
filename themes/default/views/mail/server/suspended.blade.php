@component('mail::message')
# {{__('Your servers have been suspended!')}}

<x-mail::panel>
  @foreach ($servers as $server)
    <a href="{{ $pterodactylSettings->panel_url }}/server/{{ $server->identifier }}" target="_blank">{{ $server->name }}</a><br>
  @endforeach
</x-mail::panel>

{{__('To automatically re-enable your server/s, you need to purchase more credits.')}}

<x-mail::button :url="route('store.index')">
  {{ __('Purchase Credits') }}
</x-mail::button>

{{ __('If you have any questions please let us know.') }}<br>
{{ config('app.name') }}
@endcomponent
