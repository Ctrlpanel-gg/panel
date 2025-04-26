@component('mail::message')
Ticket #{{$ticket->ticket_id}} has been opened by **{{$user->name}}**

### Details:
Client: {{$user->name}} <br>
Subject: {{$ticket->title}} <br>
Category: {{ $ticket->ticketcategory->name }} <br>
Priority: {{ $ticket->priority }} <br>
Status: {{ $ticket->status }} <br>

___
```
{{ $ticket->message }}
```
___
<br>
You can respond to this ticket by simply replying to this email or through the admin area at the url below.
<br>

{{ route('admin.ticket.show', ['ticket_id' => $ticket->ticket_id]) }}

<br>
{{__('Thanks')}},<br>
{{ config('app.name') }}
@endcomponent
