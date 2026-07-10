<?php

namespace App\Notifications;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Notifications\Notification;

class LeadRecontactReminder extends Notification
{
    public string $url;

    public function __construct(public Customer $lead)
    {
        $this->url = url('/leads/' . $lead->id);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
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
}
