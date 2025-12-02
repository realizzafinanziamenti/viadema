<?php

namespace App\Livewire\Admin\Customer;

use App\Enums\CustomerStatus;
use App\Exports\CustomersExport;
use App\Models\Customer;
use App\Traits\HandlesEntityActions;
use App\Traits\WithBulkSelection;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Masmerise\Toaster\Toaster;

class CustomerIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions, WithBulkSelection;

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
     * Ensure that at least one lead is selected.
     */
    private function ensureSelectedCustomers(): bool
    {
        if (empty($this->selected)) {
            Toaster::error("Seleziona almeno un profilo per procedere con l'esportazione.");
            return false;
        }

        return true;
    }

    /**
     * Export customers based on selected IDs
     */
    public function exportSelectedCustomers()
    {
        Gate::authorize('exportCustomer', Customer::class);

        if (!$this->ensureSelectedCustomers()) {
            return;
        }

        try {
            $query = Customer::whereIn('id', $this->selected);

            return Excel::download(
                new CustomersExport($query),
                'clienti_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
            );
        } catch (Exception $e) {
            Log::error('Errore durante l\'export customer: ' . $e->getMessage(), [
                'selected_customers' => $this->selected,
                'user_id' => auth()->id(),
            ]);

            Toaster::error('Errore durante l\'esportazione dei profili. Riprova più tardi.');
            return;
        }
    }

    /**
     * Updated search bar callback function
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function query()
    {
        return Customer::with('user')
            ->customers()
            ->filteredForDepartment()
            ->filterBySearch($this->search)
            ->orderByDesc('updated_at');
    }

    #[Computed]
    public function rows()
    {
        return $this->query()
            ->paginate(15);
    }

    public function mount()
    {
        Gate::authorize('viewAny', [Customer::class, CustomerStatus::CUSTOMER]);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.customer.customer-index');
    }
}
