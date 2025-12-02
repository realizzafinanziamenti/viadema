<?php

namespace App\Notifications;

use App\Models\Practice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PracticeRenewabilityAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Practice $practice)
    {
        $this->url = url('/practices/details/' . $this->practice->id);
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
            ->subject('Avviso Scadenza Pratica')
            ->greeting('Ciao!')
            ->line('Scadenza pratica ' . $this->practice->practice_code . ' imminente.')
            ->line('Chiamare ' . $this->practice->customer?->full_name . ' per rinnovo pratica.')
            ->action('Vai alla pratica', $this->url);
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
            'title' => 'Avviso Scadenza Pratica',
            'message' => 'Chiamare ' . $this->practice->customer?->full_name . ' per rinnovo pratica.',
            'url' => $this->url,
            'type' => 'practice-renewability-alert',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'practice-renewability-alert';
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
        return 'broadcast.practice-renewability-alert';
    }
}
