<?php

namespace App\Livewire\Admin\Setting;

use App\Models\CustomerType;
use App\Rules\UniqueNormalized;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class CustomerTypeManager extends Component
{
    use HandlesEntityActions, WithPagination, WithoutUrlPagination;

    public ?CustomerType $selectedCustomerType = null;
    public ?string $name = null;

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
        ];
    }

    /**
     * This method is called when the user clicks the create button.
     * It resets name, then opens the modal for creating a new customer type.
     */
    public function openCreateCustomerTypeModal(): void
    {
        $this->reset(['name', 'selectedCustomerType']);
        $this->resetValidation();
        $this->dispatch('open-modal', 'create-customer-type');
    }

    /**
     * This method is called when the user clicks the create button in the modal.
     * It creates a new customer type and resets the name to null.
     */
    public function createCustomerType(): void
    {
        Gate::authorize('create', CustomerType::class);

        $this->validate(['name' => ['required', 'string', 'max:255', new UniqueNormalized('customer_types', 'name')]]);

        try {
            CustomerType::create(['name' => $this->name]);
            Toaster::success('Tipologia cliente creata con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante la creazione della tipologia cliente: ' . $e->getMessage());
        }

        $this->reset('name');
        $this->dispatch('close-modal', 'create-customer-type');
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected customer type to be deleted.
     */
    public function selectCustomerTypeForDelete(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: CustomerType::class,
            property: 'selectedCustomerType',
            modalName: 'delete-customer-type',
            notFoundMessage: 'Tipologia cliente non trovata'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected customer type and resets the selected customer type to null.
     */
    public function deleteCustomerType(): void
    {
        Gate::authorize('delete', $this->selectedCustomerType);

        $this->deleteSelectedEntity(
            property: 'selectedCustomerType',
            modalName: 'delete-customer-type',
            successMessage: 'Tipologia cliente eliminata con successo'
        );

        $this->reset('selectedCustomerType');
        $this->resetPage();
    }

    public function render()
    {
        $customerTypes = CustomerType::orderBy('name')->paginate(10);

        return view('livewire.admin.setting.customer-type-manager', [
            'customerTypes' => $customerTypes,
        ]);
    }
}
