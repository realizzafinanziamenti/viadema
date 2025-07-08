<?php

namespace App\Livewire\Layout;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class LogoutButton extends Component
{
    public function logout()
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        $this->redirect('/', navigate: true);
    }

    public function render()
    {
        return view('livewire.layout.logout-button');
    }
}
