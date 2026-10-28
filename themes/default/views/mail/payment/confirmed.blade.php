@component('mail::message')
# {{ __('Thank you for your purchase!') }}
{{ __('Your payment has been confirmed; Your account has been updated.') }}<br>

# Details
___
### {{ __('Payment ID') }}: **{{ $payment->id }}**<br>
### {{ __('Status') }}: **{{ $payment->status }}**<br>
### {{ __('Price') }}: **{{ Currency::formatToCurrency($payment->total_price, $payment->currency_code) }}**<br>
### {{ __('Type') }}: **{{ $payment->type == 'Credits' ? app(App\Settings\GeneralSettings::class)->credits_display_name : $payment->type }}**<br>
### {{ __('Amount') }}: **{{ $payment->type == 'Credits' ? Currency::formatForDisplay($payment->amount) : $payment->amount }}**<br>
### {{ __('Balance') }}: **{{ Currency::formatForDisplay($payment->user->credits) }}**<br>
### {{ __('User ID') }}: **{{ $payment->user_id }}**<br>

<br>
{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent
