<?php

namespace App\Livewire\Admin\Customer;

use App\Livewire\Forms\CustomerForm;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CustomerUpdate extends Component
{
    public Customer $customer;
    // customer form component
    public CustomerForm $form;
    public string $search = '';

    /**
     * edit assignment
     */
    public function save(): void
    {
        Gate::authorize('update', $this->customer);
        $customer = $this->form->update();

        $this->redirectRoute('customer.show', ['id' => $customer->id], navigate: true);
    }

    public function mount($id)
    {
        $this->customer = Customer::findOrFail($id);
        Gate::authorize('update', $this->customer);

        $this->form->setCustomer($this->customer);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $teamMembers = User::teamMembers()
            ->filterBySearch($this->search)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.customer.customer-update', [
            'teamMembers' => $teamMembers,
        ]);
    }
}
