<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class ChatButton extends Component
{
    public $showNotificationBadge = false;

    /**
     * Listeners
     */
    public function getListeners()
    {
        $authUser = auth()->user();
        $encodedType = \Namu\WireChat\Helpers\MorphClassResolver::encode($authUser->getMorphClass());
        $authId = $authUser->id;

        return [
            "echo-private:participant.{$encodedType}.{$authId},.Namu\\WireChat\\Events\\NotifyParticipant" => 'badgeNotificationUpdate',
        ];
    }

    /**
     * Update the notification badge
     */
    public function badgeNotificationUpdate()
    {
        $this->showNotificationBadge = auth()->user()->getUnreadCount() > 0;
        // Play notification sound - only if the badge is not visible
        $this->showNotificationBadge ? $this->dispatch('play-notification-sound') : null;
    }

    public function mount()
    {
        $this->showNotificationBadge = auth()->user()->getUnreadCount() > 0;
    }

    public function render()
    {
        return view('livewire.layout.chat-button');
    }
}
