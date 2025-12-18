<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportExcelCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $importType = 'practices')  // 'leads' or 'practices'
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->setTitle(),
            'message' => $this->setMessage(),
            'url' => $this->setUrl(),
            'type' => $this->importType,
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'import-excel-completed';
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
        return 'import-excel-completed';
    }

    /**
     * Set the title based on import type
     */
    protected function setTitle(): string
    {
        return match ($this->importType) {
            'leads' => 'Import Leads Completato',
            'practices' => 'Import Pratiche Completato',
            default => 'Import Completato',
        };
    }

    /**
     * Set the message based on import type
     */
    protected function setMessage(): string
    {
        return match ($this->importType) {
            'leads' => 'Tentativo di import leads completato',
            'practices' => 'Tentativo di import pratiche completato',
            default => 'Tentativo di import completato',
        };
    }

    /**
     * Set the URL based on import type
     */
    protected function setUrl(): string
    {
        return match ($this->importType) {
            'leads' => url('/leads'),
            'practices' => url('/practices'),
            default => url('/'),
        };
    }
}
