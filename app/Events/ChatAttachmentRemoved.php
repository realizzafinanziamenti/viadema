<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChatAttachmentRemoved implements ShouldBroadcast
{
    public string $queue = 'messages';

    public function __construct(public int $conversationId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('conversation.'.$this->conversationId)];
    }

    public function broadcastAs(): string
    {
        return 'chat.attachment-removed';
    }
}
