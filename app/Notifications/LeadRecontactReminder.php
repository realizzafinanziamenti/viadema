<?php
namespace App\Notifications;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadRecontactReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public string $url;

    public function __construct(public Customer $lead)
    {
        $this->url = url('/leads/' . $lead->id);
        $this->afterCommit();
    }

    public function via(object $notifiable): array
{
    return ['database', 'broadcast'];
}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ricontatto lead richiesto')
            ->greeting('Ciao ' . $notifiable->name)
            ->line("È il momento di ricontattare il lead **{$this->lead->full_name}**.")
            ->line('Data ricontatto: ' . $this->lead->recontact_date?->format('d/m/Y'))
            ->action('Visualizza Lead', $this->url)
            ->salutation('Grazie');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ricontatto Lead',
            'message' => "Ricontattare {$this->lead->full_name} in data " . $this->lead->recontact_date?->format('d/m/Y') . ".",
            'lead_id' => $this->lead->id,
            'recontact_date' => $this->lead->recontact_date?->toDateString(),
            'url' => $this->url,
            'type' => 'lead-recontact-reminder',
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'lead-recontact-reminder';
    }

    public function initialDatabaseReadAtValue(): ?Carbon
    {
        return null;
    }

    public function broadcastType(): string
    {
        return 'broadcast.lead-recontact-reminder';
    }
}