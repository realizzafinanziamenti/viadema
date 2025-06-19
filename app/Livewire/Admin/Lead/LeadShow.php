<?php

namespace App\Livewire\Admin\Lead;

use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class LeadShow extends Component
{
    public Customer $lead;

    public function mount($id)
    {
        $this->lead = Customer::with('customerType')->findOrFail($id);
        Gate::authorize('view', $this->lead);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.lead.lead-show');
    }
}
