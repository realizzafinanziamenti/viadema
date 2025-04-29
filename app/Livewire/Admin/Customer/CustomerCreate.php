<?php

namespace App\Livewire\Admin\Customer;

use App\Livewire\Forms\CustomerForm;
use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CustomerCreate extends Component
{
    // customer form component
    public CustomerForm $form;

    /**
     * Save customer
     */
    public function save(): void
    {
        Gate::authorize('create', Customer::class);
        $customer = $this->form->store();

        $this->redirectRoute('customer.show', ['id' => $customer->id], navigate: true);
    }

    public function mount()
    {
        Gate::authorize('create', Customer::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.customer.customer-create');
    }
}
