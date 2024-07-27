@component('mail::message')
  {{$body}}

  <br>
  {{__('Thanks')}},<br>
  {{ config('app.name') }}
@endcomponent
