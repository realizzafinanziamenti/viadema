<?php

namespace App\Livewire\Admin\Practice;

use Livewire\Attributes\Layout;
use Livewire\Component;

class PracticeCreate extends Component
{


    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.practice.practice-create');
    }
}
