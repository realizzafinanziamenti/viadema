<?php

namespace App\Notifications;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Event $event,
        public string $action, // 'removed', 'modified', 'cancelled'
    ) {
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
        $subject = $this->getMailMessages() ?? 'Aggiornamento evento';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Ciao ' . $notifiable->full_name . '!');

        if ($notifiable->id === $this->event->user_id) {
            $mail->line('Il tuo evento "' . $this->event->title . '" è stato ' . $this->translatedAction() . ' da un amministratore.');
        } else {
            $mail->line($subject . ': ' . $this->event->title);
        }

        // add date, time and action link only for modified actions
        if (in_array($this->action, ['modified'])) {
            $mail->line('Data: ' . $this->event->formattedStartDate . ' dalle ' . $this->event->formattedStartTime . ' alle ' . $this->event->formattedEndTime)
                ->action('Visualizza Evento', route('calendar', ['date' => $this->event->start_date->format('Y-m-d')]));
        }

        return $mail->line('Grazie per utilizzare la nostra applicazione!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $databaseMessages = $this->getDatabaseMessages();

        if ($notifiable->id === $this->event->user_id) {
            $message = 'Il tuo evento "' . $this->event->title . '" è stato ' . $this->translatedAction() . ' da un amministratore.';
        } else {
            $message = ($databaseMessages ?? 'Aggiornamento evento') . ': ' . $this->event->title;
        }

        return [
            'event_id' => $this->event->id,
            'title' => $databaseMessages ?? 'Aggiornamento evento',
            'message' => $message,
            'url' => in_array($this->action, ['modified'])
                ? route('calendar', ['date' => $this->event->start_date->format('Y-m-d')])
                : null,
            'type' => 'event-updated',
            'action' => $this->action,
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'event-updated';
    }

    public function broadcastType(): string
    {
        return 'broadcast.event-updated';
    }

    public function initialDatabaseReadAtValue(): ?Carbon
    {
        return null;
    }

    /**
     * Get mail messages
     */
    protected function getMailMessages(): string
    {
        return match ($this->action) {
            'removed' => 'Sei stato rimosso dall\'evento',
            'modified' => 'L\'evento è stato modificato',
            'cancelled' => 'L\'evento è stato annullato',
            default => 'Aggiornamento evento'
        };
    }

    /**
     * Get database messages
     */
    protected function getDatabaseMessages(): string
    {
        return match ($this->action) {
            'removed' => 'Sei stato rimosso da un evento',
            'modified' => 'Un evento è stato modificato',
            'cancelled' => 'Un evento è stato annullato',
            default => 'Aggiornamento evento'
        };
    }

    /**
     * Get translated action for frontend display
     */
    public function translatedAction(): string
    {
        return match ($this->action) {
            'removed' => 'rimosso',
            'modified' => 'modificato',
            'cancelled' => 'annullato',
            default => 'aggiornato'
        };
    }
}
