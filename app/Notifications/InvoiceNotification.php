<?php

namespace App\Notifications;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use LaravelDaily\Invoices\Invoice;

class InvoiceNotification extends Notification

{
    use Queueable;
    /**
     * @var invoice
     */
    private $invoice;

    /**
     * Create a new notification instance.
     *
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => "Invoice Created: Nr.".$this->invoice->sequence,
            'content' => "
                <p>Find it <a href='".$this->invoice->url()."'>here</a>.</p>
            ",
        ];
    }
}
