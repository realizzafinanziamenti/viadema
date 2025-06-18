<?php

namespace App\Livewire\Admin\Setting;

use App\Models\CustomerType;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
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

        $this->validate(['name' => ['required', 'string', 'max:255', Rule::unique('customer_types', 'name')]]);

        try {
            CustomerType::create(['name' => $this->name]);
            Toaster::success('Tipologia cliente creata con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante la creazione della tipologia cliente: ' . $e->getMessage());
        }

        $this->name = null;
        $this->dispatch('close-modal', 'create-customer-type');
    }

    /**
     * This method is called when the user clicks the update button.
     * It sets the selected customer type and opens the modal for updating the name.
     */
    public function selectCustomerTypeForUpdate(int $id)
    {
        $this->resetValidation();
        $this->selectEntityForAction(
            id: $id,
            modelClass: CustomerType::class,
            property: 'selectedCustomerType',
            modalName: 'update-customer-type',
            notFoundMessage: 'Tipologia cliente non trovata'
        );
        $this->name = $this->selectedCustomerType->name;
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the customer type and resets the name to null.
     */
    public function updateCustomerType(): void
    {
        Gate::authorize('update', $this->selectedCustomerType);

        $this->validate(['name' => ['required', 'string', 'max:255', Rule::unique('customer_types', 'name')->ignore($this->selectedCustomerType?->id)]]);

        try {
            $this->selectedCustomerType->update(['name' => $this->name]);
            Toaster::success('Tipologia cliente aggiornata con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento della tipologia cliente: ' . $e->getMessage());
        }

        $this->reset(['name', 'selectedCustomerType']);
        $this->dispatch('close-modal', 'update-customer-type');
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
    }

    public function render()
    {
        $customerTypes = CustomerType::orderBy('name')->paginate(10);

        return view('livewire.admin.setting.customer-type-manager', [
            'customerTypes' => $customerTypes,
        ]);
    }
}
