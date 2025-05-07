<?php

namespace App\Livewire\Admin\Practice;

use App\Livewire\Forms\CustomerForm;
use App\Livewire\Forms\PracticeForm;
use App\Models\Customer;
use App\Models\Practice;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PracticeCreate extends Component
{
    public CustomerForm $customerForm;
    public PracticeForm $practiceForm;
    public ?Customer $selectedCustomer = null;
    public int $step = 1;
    public string $teamMemberSearch = '';
    public string $customerSearch = '';

    /**
     * updated practice form userId callback function
     */
    public function updatedPracticeFormUserId($id): void
    {
        $this->resetValidation('practiceForm.userId');
        $this->selectedCustomer = Customer::find($id);
    }

    /**
     * first next step function
     */
    public function firstNextStep(): void
    {
        if (! $this->practiceForm->userId) {
            $this->addError('practiceForm.userId', 'Seleziona prima un cliente.');
            return;
        }

        $this->step = 2;
    }

    /**
     * first previous step function
     */
    public function firstPrevStep(): void
    {
        $this->step = 1;
    }

    public function mount()
    {
        Gate::authorize('create', Practice::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $teamMembers = User::teamMembers()
            ->filterBySearch($this->teamMemberSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        $customers = Customer::filterBySearch($this->customerSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.practice.practice-create', [
            'teamMembers' => $teamMembers,
            'customers' => $customers,
        ]);
    }
}
