<?php

namespace App\Livewire\Admin\Setting;

use App\Models\ProductSubtype;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class ProductSubtypeManager extends Component
{
    use HandlesEntityActions, WithPagination, WithoutUrlPagination;

    public ?ProductSubtype $selectedProductSubtype = null;
    public ?string $name = null;

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
        ];
    }

    /**
     * This method is called when the user clicks the create button.
     * It resets name, then opens the modal for creating a new ProductSubtype.
     */
    public function openCreateProductSubtypeModal(): void
    {
        $this->reset(['name', 'selectedProductSubtype']);
        $this->resetValidation();
        $this->dispatch('open-modal', 'create-product-subtype');
    }

    /**
     * This method is called when the user clicks the create button in the modal.
     * It creates a new ProductSubtype and resets the name to null.
     */
    public function createProductSubtype(): void
    {
        Gate::authorize('create', ProductSubtype::class);

        $this->validate(['name' => ['required', 'string', 'max:255', Rule::unique('product_subtypes', 'name')]]);

        try {
            ProductSubtype::create(['name' => $this->name]);
            Toaster::success('Tipo prodotto creato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante la creazione del tipo prodotto: ' . $e->getMessage());
        }

        $this->name = null;
        $this->dispatch('close-modal', 'create-product-subtype');
    }

    /**
     * This method is called when the user clicks the update button.
     * It sets the selected ProductSubtype and opens the modal for updating the name.
     */
    public function selectProductSubtypeForUpdate(int $id)
    {
        $this->resetValidation();
        $this->selectEntityForAction(
            id: $id,
            modelClass: ProductSubtype::class,
            property: 'selectedProductSubtype',
            modalName: 'update-product-subtype',
            notFoundMessage: 'Tipo prodotto non trovato'
        );
        $this->name = $this->selectedProductSubtype->name;
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the ProductSubtype and resets the name to null.
     */
    public function updateProductSubtype(): void
    {
        Gate::authorize('update', $this->selectedProductSubtype);

        $this->validate(['name' => ['required', 'string', 'max:255', Rule::unique('product_subtypes', 'name')->ignore($this->selectedProductSubtype?->id)]]);

        try {
            $this->selectedProductSubtype->update(['name' => $this->name]);
            Toaster::success('Tipo prodotto aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento del tipo prodotto: ' . $e->getMessage());
        }

        $this->reset(['name', 'selectedProductSubtype']);
        $this->dispatch('close-modal', 'update-product-subtype');
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected ProductSubtype to be deleted.
     */
    public function selectProductSubtypeForDelete(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: ProductSubtype::class,
            property: 'selectedProductSubtype',
            modalName: 'delete-product-subtype',
            notFoundMessage: 'Tipo prodotto non trovato'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected ProductSubtype and resets the selected ProductSubtype to null.
     */
    public function deleteProductSubtype(): void
    {
        Gate::authorize('delete', $this->selectedProductSubtype);

        $this->deleteSelectedEntity(
            property: 'selectedProductSubtype',
            modalName: 'delete-product-subtype',
            successMessage: 'Tipo prodotto eliminato con successo'
        );
    }

    public function render()
    {
        $productSubtypes = ProductSubtype::orderBy('name')->paginate(10);

        return view('livewire.admin.setting.product-subtype-manager', [
            'productSubtypes' => $productSubtypes,
        ]);
    }
}
