<?php

namespace App\Livewire\Admin\Lead;

use Livewire\Attributes\Layout;
use Livewire\Component;

class LeadUpdate extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.lead.lead-update');
    }
}
