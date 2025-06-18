<?php

namespace App\Livewire\Admin\Lead;

use Livewire\Attributes\Layout;
use Livewire\Component;

class LeadCreate extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.lead.lead-create');
    }
}
