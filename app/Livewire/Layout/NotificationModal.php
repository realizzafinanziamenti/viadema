<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class NotificationModal extends Component
{
    public bool $show = false;

    public function refreshNotifications()
    {
        dd('ciao');
    }

    public function render()
    {
        return view('livewire.layout.notification-modal');
    }
}
