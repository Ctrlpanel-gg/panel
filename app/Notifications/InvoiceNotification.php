<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use LaravelDaily\Invoices\Invoice;

class InvoiceNotification extends Notification

{
    use Queueable;

    /**
     * @var invoice
     *      * @var invoice
     *      * @var invoice
     */
    private $invoice;
    private $user;
    private $payment;

    /**
     * Create a new notification instance.
     *
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice, User $user, Payment $payment)
    {
        $this->invoice = $invoice;
        $this->user = $user;
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your Payment was successful!')
            ->greeting('Hello,')
            ->line("Your payment was processed successfully!")
            ->line('Status: ' . $this->payment->status)
            ->line('Price: ' . $this->payment->formatToCurrency($this->payment->total_price))
            ->line('Type: ' . $this->payment->type)
            ->line('Amount: ' . $this->payment->amount)
            ->line('Balance: ' . number_format($this->user->credits,2))
            ->line('User ID: ' . $this->payment->user_id)
            ->attach(storage_path('app/invoice/' . $this->user->id . '/' . now()->format('Y') . '/' . $this->invoice->filename));
    }
}
