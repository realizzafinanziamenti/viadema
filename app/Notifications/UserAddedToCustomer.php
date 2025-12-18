<?php

namespace App\Notifications;

use App\Models\Customer;
use Carbon\Carbon;
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
        $config = $this->config();

        return (new MailMessage)
            ->subject($config['title'])
            ->greeting('Ciao ' . $notifiable->full_name . '!')
            ->line($config['message'])
            ->action($config['action'], route($config['route'], ['id' => $this->customer->id]))
            ->line('Grazie per utilizzare la nostra applicazione!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $config = $this->config();

        return [
            'lead_id' => $this->customer->id,
            'title' => $config['title'],
            'message' => $config['message'],
            'url' => route($config['route'], ['id' => $this->customer->id]),
            'type' => $config['db_type'],
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return $this->config()['db_type'];
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
        return $this->config()['broadcast_type'];
    }

    /**
     * Get configuration based on customer type.
     */
    protected function config(): array
    {
        if ($this->customer->isLead()) {
            return [
                'title' => 'Lead assegnato',
                'message' => 'Ti è stato assegnato un nuovo lead.',
                'route' => 'lead.show',
                'action' => 'Visualizza Lead',
                'db_type' => 'user-added-to-lead',
                'broadcast_type' => 'broadcast.user-added-to-lead',
            ];
        }

        return [
            'title' => 'Cliente assegnato',
            'message' => 'Ti è stato assegnato un nuovo cliente.',
            'route' => 'customer.show',
            'action' => 'Visualizza Cliente',
            'db_type' => 'user-added-to-customer',
            'broadcast_type' => 'broadcast.user-added-to-customer',
        ];
    }
}
