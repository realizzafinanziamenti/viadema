<?php

namespace App\Livewire\Admin\Simulator;

use Livewire\Attributes\Layout;
use Livewire\Component;

class SimulatorIndex extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.simulator.simulator-index');
    }
}
