<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $receiverId;

    public function __construct($data, $receiverId)
    {
        $this->data = $data;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new Channel('video-chat.' . $this->receiverId);
    }

    public function broadcastAs()
    {
        return 'video';
    }

    public function broadcastWith()
    {
        return [
            'receiver' => $this->receiverId,
            'data' => $this->data
        ];
    }
}
