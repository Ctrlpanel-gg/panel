@component('mail::message')
# Thank you for your purchase!
Your payment has been confirmed; Your credit balance has been updated.<br>

# Details
___
### Payment ID: **{{$payment->id}}**<br>
### Status:     **{{$payment->status}}**<br>
### Price:      **{{$payment->formatCurrency()}}**<br>
### Type:       **{{$payment->type}}**<br>
### Amount:     **{{$payment->amount}}**<br>
### Balance:    **{{$payment->user->credits}}**<br>
### User ID:    **{{$payment->user_id}}**<br>

<br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent
