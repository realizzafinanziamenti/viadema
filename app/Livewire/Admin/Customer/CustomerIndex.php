<?php

namespace App\Livewire\Admin\Customer;

use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class CustomerIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions;

    public Customer|null $selectedCustomer = null;
    public $search = '';

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected customer to be deleted.
     */
    public function selectCustomerForDelete(int $id)
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Customer::class,
            property: 'selectedCustomer',
            modalName: 'delete-customer',
            notFoundMessage: 'Cliente non trovato'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected customer and resets the selected customer to null.
     */
    public function deleteCustomer()
    {
        Gate::authorize('delete', $this->selectedCustomer);

        $this->deleteSelectedEntity(
            property: 'selectedCustomer',
            modalName: 'delete-customer',
            successMessage: 'Cliente eliminato con successo',
        );
    }

    /**
     * Updated search bar callback function
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function mount()
    {
        Gate::authorize('viewAny', [Customer::class, CustomerStatus::CUSTOMER]);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Customer::with('user')
            ->customers()
            ->orderByDesc('updated_at');

        $query = $query->filterBySearch($this->search);
        $customers = $query->paginate(15);

        return view('livewire.admin.customer.customer-index', [
            'customers' => $customers,
        ]);
    }
}
