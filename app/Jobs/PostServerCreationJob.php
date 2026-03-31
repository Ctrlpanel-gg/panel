<?php

namespace App\Jobs;

use App\Events\ServerCreatedEvent;
use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PostServerCreationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $serverId;

    public function __construct(string $serverId)
    {
        $this->serverId = $serverId;
        $this->queue = 'default';
    }

    public function handle(): void
    {
        $server = Server::with('user')->find($this->serverId);

        if (!$server || $server->status !== Server::STATUS_ACTIVE) {
            return;
        }

        if (!$server->user) {
            return;
        }

        event(new ServerCreatedEvent($server->user, $server));
    }
}
