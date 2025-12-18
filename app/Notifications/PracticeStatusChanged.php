<?php

namespace App\Notifications;

use App\Models\Practice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticeStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Practice $practice, public string $oldStatus, public string $newStatus)
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
            ->subject('Stato pratica aggiornato')
            ->greeting('Ciao ' . $notifiable->full_name . '!')
            ->line('Una pratica ha cambiato stato da ' . $this->oldStatus . ' a ' . $this->newStatus . '.')
            ->action('Visualizza pratica', route('practice.show', ['id' => $this->practice->id]))
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
            'practice_id' => $this->practice->id,
            'title' => 'Stato pratica aggiornato',
            'message' => 'Una pratica ha cambiato stato da ' . $this->oldStatus . ' a ' . $this->newStatus . '.',
            'url' => route('practice.show', ['id' => $this->practice->id]),
            'type' => 'practice-status-changed',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'user-added-to-practice';
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
        return 'broadcast.user-added-to-practice';
    }
}
