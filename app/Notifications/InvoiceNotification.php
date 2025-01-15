<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $invoice_file;
    private $user;
    private $payment;

    public function __construct(string $invoice_file, User $user, Payment $payment)
    {
        $this->invoice_file = $invoice_file;
        $this->user = $user;
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Your Payment was successful!'))
            ->greeting(__('Hello').',')
            ->line(__('Your payment was processed successfully!'))
            ->line(__('Status').': '.$this->payment->status->value)
            ->line(__('Price').': '.$this->payment->formatToCurrency($this->payment->total_price))
            ->line(__('Type').': '.$this->payment->type)
            ->line(__('Amount').': '.$this->payment->amount)
            ->line(__('Balance').': '.number_format($this->user->credits, 2))
            ->line(__('User ID').': '.$this->payment->user_id)
            ->attach(storage_path('app/invoice/'.$this->user->id.'/'.now()->format('Y').'/'.$this->invoice_file));
    }
}
