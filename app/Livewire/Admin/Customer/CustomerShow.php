<?php

namespace App\Livewire\Admin\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CustomerShow extends Component
{
    public Customer $customer;

    public function mount($id)
    {
        $this->customer = Customer::findOrFail($id);
        Gate::authorize('view', $this->customer);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.customer.customer-show');
    }
}
