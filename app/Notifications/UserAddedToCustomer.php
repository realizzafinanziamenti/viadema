<?php

namespace App\Notifications;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAddedToCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Customer $customer)
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
            ->subject($this->getTitle())
            ->greeting('Ciao ' . $notifiable->full_name . '!')
            ->line($this->getMessage())
            ->action($this->getUrlMessage(), route($this->getUrl(), ['id' => $this->customer->id]))
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
            'lead_id' => $this->customer->id,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
            'url' => route($this->getUrl(), ['id' => $this->customer->id]),
            'type' => $this->databaseType($notifiable),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        if ($this->customer->isLead()) {
            return 'user-added-to-lead';
        } else {
            return 'user-added-to-customer';
        }
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
        if ($this->customer->isLead()) {
            return 'broadcast.user-added-to-lead';
        } else {
            return 'broadcast.user-added-to-customer';
        }
    }

    /**
     * Get title
     */
    protected function getTitle(): string
    {
        if ($this->customer->isLead()) {
            return 'Lead assegnato';
        } else {
            return 'Cliente assegnato';
        }
    }

    /**
     * Get message
     */
    protected function getMessage(): string
    {
        if ($this->customer->isLead()) {
            return 'Ti è stato assegnato un nuovo lead.';
        } else {
            return 'Ti è stato assegnato un nuovo cliente.';
        }
    }

    /**
     * Get url
     */
    protected function getUrl(): string
    {
        if ($this->customer->isLead()) {
            return 'lead.show';
        } else {
            return 'customer.show';
        }
    }

    /**
     * Get url message
     */
    protected function getUrlMessage(): string
    {
        if ($this->customer->isLead()) {
            return 'Visualizza Lead';
        } else {
            return 'Visualizza Cliente';
        }
    }
}
