<?php

namespace App\Notifications;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadFollowUp extends Notification implements ShouldQueue
{
    use Queueable;

    public $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Customer $lead)
    {
        $this->url = url('/leads/' . $lead->id);
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
            ->subject('Follow-up richiesto: Lead non contattato')
            ->greeting('Ciao ' . $notifiable->name)
            ->line("Il lead **{$this->lead->full_name}** è stato inserito 6 ore fa e non è stato ancora contattato.")
            ->line('È necessario contattare il lead il prima possibile.')
            ->action('Visualizza Lead', $this->url)
            ->salutation('Grazie');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Follow-up Lead Richiesto',
            'message' => "Il lead {$this->lead->full_name} necessita di essere contattato.",
            'lead_id' => $this->lead->id,
            'url' => $this->url,
            'type' => 'lead-follow-up',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'lead-follow-up';
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
        return 'broadcast.lead-follow-up';
    }
}
