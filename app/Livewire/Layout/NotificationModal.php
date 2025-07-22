<?php

namespace App\Livewire\Layout;

use Carbon\Carbon;
use Livewire\Component;

class NotificationModal extends Component
{
    public bool $show = false;
    public $notifications;
    public $renewabilityNotifications;
    public $otherNotifications;

    public int $notificationsCount = 0; // Total count of notifications
    public int $unreadNotificationsCount = 0; // Count of total unread notifications

    public int $renewabilityNotificationsCount = 0; // Count of renewability notifications
    public int $unreadRenewabilityNotificationsCount = 0; // Count of unread renewability notifications

    public int $otherNotificationsCount = 0; // Count of other notifications
    public int $unreadOtherNotificationsCount = 0; // Count of unread other notifications

    public int $renewabilityLimit = 10;
    public int $otherLimit = 10;

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
        $this->renewabilityNotificationsCount = auth()->user()
            ->notifications()
            ->where('type', 'practice-renewability-alert')
            ->count();

        $this->otherNotificationsCount = auth()->user()
            ->notifications()
            ->where('type', '!=', 'practice-renewability-alert')
            ->count();
    }

    /**
     * Increase notification limit
     */
    public function increaseLimit(string $type)
    {
        $this->loadMoreNotifications($type);
    }

    /**
     * Load unread notifications
     */
    public function loadNotifications()
    {
        $user = auth()->user();

        $unreadNotifications = $user->unreadNotifications()->get();
        $this->unreadNotificationsCount = $unreadNotifications->count();

        $this->unreadRenewabilityNotificationsCount = $unreadNotifications->where('type', 'practice-renewability-alert')->count();
        $this->unreadOtherNotificationsCount = $unreadNotifications->where('type', '!=', 'practice-renewability-alert')->count();

        $this->renewabilityNotifications = $user->notifications()
            ->where('type', 'practice-renewability-alert')
            ->take($this->renewabilityLimit)
            ->get();

        $this->otherNotifications = $user->notifications()
            ->where('type', '!=', 'practice-renewability-alert')
            ->take($this->otherLimit)
            ->get();
    }

    /**
     * Load more notifications
     */
    public function loadMoreNotifications(string $type)
    {
        $user = auth()->user();

        if ($type === 'renewability') {
            // Load more renewability notifications
            $newRenewabilityNotifications = $user->notifications()
                ->where('type', 'practice-renewability-alert')
                ->latest()
                ->skip(count($this->renewabilityNotifications))
                ->take($this->renewabilityLimit)
                ->get();

            // Merge new renewability notifications with existing ones
            $this->renewabilityNotifications = $this->renewabilityNotifications->merge($newRenewabilityNotifications);
        } elseif ($type === 'others') {
            // Load more other notifications
            $newOtherNotifications = $user->notifications()
                ->where('type', '!=', 'practice-renewability-alert')
                ->latest()
                ->skip(count($this->otherNotifications))
                ->take($this->otherLimit)
                ->get();

            // Merge new other notifications with existing ones
            $this->otherNotifications = $this->otherNotifications->merge($newOtherNotifications);
        }
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

            // Decrease the unread notifications count
            $this->unreadNotificationsCount--;

            // Update the counts and collections based on the notification type
            if ($notification->type === 'practice-renewability-alert') {
                $this->unreadRenewabilityNotificationsCount--;
                $this->renewabilityNotifications = $this->renewabilityNotifications->map(
                    fn($n) => $n->id === $notification->id ? $n->fresh() : $n
                );
            } else {
                $this->unreadOtherNotificationsCount--;
                $this->otherNotifications = $this->otherNotifications->map(
                    fn($n) => $n->id === $notification->id ? $n->fresh() : $n
                );
            }

            // Dispatch an event to update the notification button
            $this->dispatch('refresh-notification-button')->to(NotificationButton::class);
        }
    }

    /**
     * Delete a notification
     */
    public function deleteNotification(string $id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (!$notification) return;

        $notification->delete();

        // Update the counts and collections based on the notification type
        if ($notification->type === 'practice-renewability-alert') {
            $this->renewabilityNotifications = $this->renewabilityNotifications->reject(fn($n) => $n->id === $id);
            $this->renewabilityNotificationsCount--;
            // Decrease unread counts if the notification was unread
            if (is_null($notification->read_at)) {
                $this->unreadRenewabilityNotificationsCount--;
                $this->unreadNotificationsCount--;
            }
        } else {
            $this->otherNotifications = $this->otherNotifications->reject(fn($n) => $n->id === $id);
            $this->otherNotificationsCount--;
            // Decrease unread counts if the notification was unread
            if (is_null($notification->read_at)) {
                $this->unreadOtherNotificationsCount--;
                $this->unreadNotificationsCount--;
            }
        }

        $this->notificationsCount--;

        // Update the badge
        $this->dispatch('refresh-notification-button')->to(NotificationButton::class);
    }

    /**
     * Mark all notifications as read
     */
    // public function deleteAllNotifications()
    // {
    //     auth()->user()->notifications()->delete();
    //     $this->notifications = collect();
    //     $this->unreadNotificationsCount = 0;
    //     $this->notificationsCount = 0;

    //     // Dispatch an event to update the notification button
    //     $this->dispatch('refresh-notification-button')->to(NotificationButton::class);
    //     $this->dispatch('close-modal', 'notification-modal');
    // }

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
