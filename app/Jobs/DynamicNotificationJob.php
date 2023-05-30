<?php

namespace App\Jobs;

use App\Notifications\DynamicNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class DynamicNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $users;

    /**
     * @var array
     */
    private $via;

    /**
     * @var array
     */
    private $database;

    /**
     * @var MailMessage
     */
    private $mail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($users, array $via, ?array $database, ?MailMessage $mail)
    {
        $this->users = $users;
        $this->via = $via;
        $this->database = $database;
        $this->mail = $mail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Notification::send($this->users, new DynamicNotification($this->via['via'], $this->database, $this->mail));
    }
}
