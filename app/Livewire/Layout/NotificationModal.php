<?php

namespace App\Livewire\Layout;

use Carbon\Carbon;
use Livewire\Component;

class NotificationModal extends Component
{
    public bool $show = false;
    public $notifications;
    public int $unreadNotificationsCount = 0;
    public int $notificationsCount = 0;
    public int $notificationLimit = 10;

    /**
     * Listeners
     */
    public function getListeners()
    {
        $auth_id = auth()->user()->id;
        return [
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'refreshNotifications',
        ];
    }

    /**
     * Refresh notifications
     */
    public function refreshNotifications()
    {
        $this->loadNotifications();
        $this->notificationsCount();
    }

    /**
     * total notifications count
     */
    public function notificationsCount()
    {
        $this->notificationsCount = auth()->user()
            ->notifications()
            ->count();
    }

    /**
     * Increase notification limit
     */
    public function increaseLimit()
    {
        $this->loadMoreNotifications();
    }

    /**
     * Load unread notifications
     */
    public function loadNotifications()
    {
        $user = auth()->user();

        $this->unreadNotificationsCount = $user->unreadNotifications()->count();

        $this->notifications = $user->notifications()
            ->latest()
            ->limit($this->notificationLimit)
            ->get();
    }

    /**
     * Load more notifications
     */
    public function loadMoreNotifications()
    {
        $newNotifications = auth()->user()->notifications()
            ->latest()
            ->skip(count($this->notifications))
            ->take($this->notificationLimit)
            ->get();

        // Unisce le nuove notifiche a quelle già esistenti
        $this->notifications = $this->notifications->merge($newNotifications);
    }

    /**
     * Get the label for the time range of a notification
     */
    public function getTimeRangeLabel(Carbon $date): string
    {
        if ($date->isToday()) {
            return 'Oggi';
        } elseif ($date->isYesterday()) {
            return 'Ieri';
        } elseif ($date->isTomorrow()) {
            return 'Domani';
        } elseif ($date->isFuture()) {
            return 'Prossimamente';
        } elseif ($date->greaterThanOrEqualTo(now()->subWeek())) {
            return 'Ultima settimana';
        } elseif ($date->greaterThanOrEqualTo(now()->subMonth())) {
            return 'Ultimo mese';
        } elseif ($date->greaterThanOrEqualTo(now()->subMonths(3))) {
            return 'Ultimi 3 mesi';
        } elseif ($date->greaterThanOrEqualTo(now()->subYear())) {
            return 'Ultimo anno';
        } else {
            return 'Tutti';
        }
    }

    /**
     * Check if the notification is the last of its time range
     */
    public function isLastOfRange(int $index): bool
    {
        $notifications = $this->notifications;

        if (!isset($notifications[$index + 1])) {
            return true;
        }

        $currentLabel = $this->getTimeRangeLabel($notifications[$index]->created_at);
        $nextLabel = $this->getTimeRangeLabel($notifications[$index + 1]->created_at);

        return $currentLabel !== $nextLabel;
    }

    /**
     * Redirect to notification's subject
     */
    public function redirectTo($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $this->markNotificationAsRead($notification);
            $this->dispatch('close-modal', 'notification-modal');
            $this->redirect($notification->data['url'], navigate: true);
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead($notification)
    {
        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();

            $this->unreadNotificationsCount--;

            $id = $notification->id;
            $this->notifications = $this->notifications->map(function ($n) use ($id) {
                // Controlla se l'elemento corrente è quello che deve essere aggiornato
                return $n->id === $id ? $n->fresh() : $n; // Aggiorna solo l'elemento specifico dal db con fresh
            });

            // Dispatch an event to update the notification button
            $this->dispatch('refresh-notification-button')->to(NotificationButton::class);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function deleteAllNotifications()
    {
        auth()->user()->notifications()->delete();
        $this->notifications = collect();
        $this->unreadNotificationsCount = 0;
        $this->notificationsCount = 0;

        // Dispatch an event to update the notification button
        $this->dispatch('refresh-notification-button')->to(NotificationButton::class);
        $this->dispatch('close-modal', 'notification-modal');
    }

    public function mount()
    {
        $this->loadNotifications();
        $this->notificationsCount();
    }

    public function render()
    {
        return view('livewire.layout.notification-modal');
    }
}
