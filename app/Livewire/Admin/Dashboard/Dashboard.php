<?php

namespace App\Livewire\Admin\Dashboard;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed()]
    public function greeting(): string
    {
        $hour = now()->hour;
        $greeting = $hour < 14 ? 'Buongiorno' : 'Buonasera';
        return "$greeting, " . auth()->user()->full_name . '!';
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.dashboard.dashboard');
    }
}
