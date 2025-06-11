<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class NotificationModal extends Component
{
    public bool $show = true;
    public $notifications;
    public int $unreadNotificationsCount = 0;
    public int $notificationsCount = 0;
    public int $notificationLimit = 15;

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
     * Redirect to notification's subject
     */
    public function redirectTo($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification) {
            $this->markNotificationAsRead($notification);

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
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        $this->unreadNotificationsCount = 0;

        $this->notifications = $this->notifications->map(function ($n) {
            $n->read_at = now();
            return $n;
        });

        // Dispatch an event to update the notification button
        $this->dispatch('mark-all-as-read')->to(NotificationButton::class);
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
