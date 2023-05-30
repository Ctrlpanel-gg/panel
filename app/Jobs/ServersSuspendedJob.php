<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\ServersSuspendedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class ServersSuspendedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $usersToNotify;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $usersToNotify)
    {
        $this->usersToNotify = $usersToNotify;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        User::whereIn('id', $this->usersToNotify)->chunk(100, function($users) {
            $users->each(function ($user) {
                Notification::send($user, new ServersSuspendedNotification());
            });
        });
    }
}
