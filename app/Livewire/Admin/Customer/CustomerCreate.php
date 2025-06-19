<?php

namespace App\Livewire\Admin\Customer;

use App\Enums\CustomerStatus;
use App\Livewire\Forms\CustomerForm;
use App\Models\Customer;
use App\Models\User;
use App\Traits\InteractsWithDropdowns;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CustomerCreate extends Component
{
    use InteractsWithDropdowns;

    // customer form component
    public CustomerForm $form;
    public string $search = '';
    public array $teamMembers = [];

    /**
     * Set title customer
     */
    public function setTeamMember(?int $value = null): void
    {
        $this->setFormSelectValue('userId', $value);
    }

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

        // Initialize customer status to CUSTOMER
        $this->form->customerStatus = CustomerStatus::CUSTOMER->value;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $this->teamMembers = User::teamMembers()
            ->filterBySearch($this->search)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.customer.customer-create', [
            'teamMembers' => $this->teamMembers,
            'selectedUserId' => $this->form->userId,
        ]);
    }
}
