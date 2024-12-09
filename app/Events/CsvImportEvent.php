<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CsvImportEvent implements ShouldBroadcast
{
    public function __construct(public $message, private $channel) {}
    public $queue = "imp";

    public function broadcastOn()
    {
        return new Channel('csv-' . $this->channel);
    }

    public function broadcastAs(): string
    {
        return 'csv-import-activity';
    }
    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}
