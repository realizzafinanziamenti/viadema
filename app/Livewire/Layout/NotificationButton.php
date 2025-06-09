<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class NotificationButton extends Component
{
    // notifications property
    public $notifications;
    // unread and total notifications count properties
    public $unreadCount;
    public $totalNotificationsCount;
    // notification types property
    public $notificationTypes = ['practice-renewability-alert'];
    // notification limit and step properties
    public $notificationLimit = 15;

    /**
     * Listeners
     */
    public function getListeners()
    {
        $auth_id = auth()->user()->id;
        return [
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'broadcastNotificationUpdate',
        ];
    }

    public function broadcastNotificationUpdate()
    {
        $this->loadNotifications();
        $this->totalNotificationsCount();
        $this->dispatch('play-notification-sound');
    }

    /**
     * total notifications count
     */
    public function totalNotificationsCount()
    {
        $this->totalNotificationsCount = auth()->user()->notifications()->whereIn('type', $this->notificationTypes)->count();
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

        $this->unreadCount = $user->unreadNotifications()->count();

        $this->notifications = $user->notifications()
            ->whereIn('type', $this->notificationTypes)
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
            ->whereIn('type', $this->notificationTypes)
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

            $this->unreadCount--;

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

        $this->unreadCount = 0;

        $this->notifications = $this->notifications->map(function ($n) {
            $n->read_at = now();
            return $n;
        });
    }

    public function mount()
    {
        $this->loadNotifications();
        $this->totalNotificationsCount();
    }

    public function render()
    {
        return view('livewire.layout.notification-button');
    }
}
