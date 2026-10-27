<?php

namespace App\Notifications;

use App\Models\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PracticeCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public int $practiceId;

    public string $customerName;

    public function __construct(Practice $practice, public ?int $actorId = null, public ?string $actorName = null)
    {
        $this->practiceId = $practice->getKey();
        $this->customerName = $practice->customer->full_name;
        $this->onQueue('notifications');
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function viaQueues(): array
    {
        return ['database' => 'notifications', 'broadcast' => 'notifications'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'practice_id' => $this->practiceId,
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'title' => 'Nuova pratica inserita',
            'message' => 'È stata inserita una nuova pratica per '.$this->customerName.'.'
                .($this->actorName ? ' Inserita da '.$this->actorName.'.' : ''),
            'url' => route('practice.show', ['id' => $this->practiceId]),
            'type' => 'practice-created',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onQueue('notifications');
    }

    public function databaseType(object $notifiable): string
    {
        return 'practice-created';
    }

    public function broadcastType(): string
    {
        return 'broadcast.practice-created';
    }
}
