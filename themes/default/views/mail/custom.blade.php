@component('mail::message')

{{ new \Illuminate\Support\HtmlString($content) }}

{{ config('app.name') }}
@endcomponent
