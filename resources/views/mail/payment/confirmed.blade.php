@component('mail::message')
# Thank you for your purchase!
Your payment has been confirmed; Your credit balance has been updated.

# Details
___
### Payment ID: **{{$payment->id}}**
### Status:     **{{$payment->status}}**
### Price:      **{{$payment->price}}**
### Type:       **{{$payment->type}}**
### Amount:     **{{$payment->amount}}**
### Balance:    **{{$payment->user->credits}}**
### User ID:    **{{$payment->user_id}}**

<br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
