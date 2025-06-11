<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class NotificationButton extends Component
{
    public int $unreadNotificationsCount = 0;

    /**
     * Listeners
     */
    public function getListeners()
    {
        $auth_id = auth()->user()->id;
        return [
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'refreshNotificationsBadge',
            "mark-all-as-read" => 'refreshNotificationsBadge',
        ];
    }

    public function refreshNotificationsBadge()
    {
        $this->unreadNotificationsCount();

        if ($this->unreadNotificationsCount > 0) {
            $this->dispatch('play-notification-sound');
        }
    }

    /**
     * total notifications count
     */
    public function unreadNotificationsCount()
    {
        $this->unreadNotificationsCount = auth()->user()
            ->unreadNotifications()
            ->count();
    }

    public function mount()
    {
        $this->unreadNotificationsCount();
    }

    public function render()
    {
        return view('livewire.layout.notification-button');
    }
}
