<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\SimpleExcel\SimpleExcelReader;

class CsvUploadEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public string $file_path, public $channel = "test", public bool $processInBatch = false) {}

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
        return ['message' => "Import will start soon"];
    }
}
