<?php

namespace App\Notifications;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAddedToEvent extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Event $event)
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Aggiunta a un evento')
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('Sei stato aggiunto all\'evento: ' . $this->event->name . ' in data ' . $this->event->formattedStartDate . ' dalle ' . $this->event->formattedStartTime . ' alle ' . $this->event->formattedEndTime . '.')
            ->action('Visualizza Evento', route('calendar', ['date' => $this->event->start_date->format('Y-m-d')]))
            ->line('Grazie per utilizzare la nostra applicazione!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'title' => 'Aggiunta a un evento',
            'message' => 'Sei stato aggiunto ad un evento in data ' . $this->event->formattedStartDate,
            'url' => route('calendar', ['date' => $this->event->start_date->format('Y-m-d')]),
            'type' => 'user-added-to-event',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'user-added-to-event';
    }

    /**
     * Get the initial value for the "read_at" column.
     */
    public function initialDatabaseReadAtValue(): ?Carbon
    {
        return null;
    }

    /**
     * Get the type of the notification being broadcast.
     */
    public function broadcastType(): string
    {
        return 'broadcast.user-added-to-event';
    }
}
