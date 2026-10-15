<?php

namespace App\Events;

use App\Models\Server;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServerCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public Server $server;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Server $server)
    {
        $this->user = $user;
        $this->server = $server;
    }
}
