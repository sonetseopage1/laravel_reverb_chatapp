<?php
// app/Events/Typing.php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Typing implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $senderId;
    public $receiver_id;

    public function __construct($senderId, $receiver_id)
    {

        $this->senderId = $senderId;
        $this->receiver_id = $receiver_id;
    }

    public function broadcastOn()
    {
        return new Channel('messagestype.' . $this->senderId);
    }

    public function broadcastAs()
    {
        return 'typing';
    }

    public function broadcastWith()
    {
        return [
            'receiver' => $this->receiver_id,
            'sender' => $this->senderId
        ];
    }
}
