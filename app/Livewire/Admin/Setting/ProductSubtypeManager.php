<?php

namespace App\Livewire\Admin\Setting;

use App\Models\ProductSubtype;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ProductSubtypeManager extends Component
{
    use HandlesEntityActions;

    public ?ProductSubtype $selectedProductSubtype = null;
    public ?string $name = null;

    /**
     * This method is called when the user clicks the create button.
     * It resets name, then opens the modal for creating a new ProductSubtype.
     */
    public function openCreateProductSubtypeModal(): void
    {
        $this->name = null;
        $this->selectedProductSubtype = null;
        $this->resetErrorBag();
        $this->dispatch('open-modal', 'create-product-subtype');
    }

    /**
     * This method is called when the user clicks the create button in the modal.
     * It creates a new ProductSubtype and resets the name to null.
     */
    public function createProductSubtype(): void
    {
        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_subtypes', 'name'),
            ],
        ]);

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
        $this->resetErrorBag();
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
        // Gate::authorize('update', $this->selectedProductSubtype);

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_subtypes', 'name')->ignore($this->selectedProductSubtype?->id),
            ],
        ]);

        try {
            $this->selectedProductSubtype->update(['name' => $this->name]);
            Toaster::success('Tipo prodotto aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento del tipo prodotto: ' . $e->getMessage());
        }

        $this->name = null;
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
        // Gate::authorize('delete', $this->selectedProductSubtype);

        $this->deleteSelectedEntity(
            property: 'selectedProductSubtype',
            modalName: 'delete-product-subtype',
            successMessage: 'Tipo prodotto eliminato con successo'
        );
    }

    public function render()
    {
        $productSubtypes = ProductSubtype::orderBy('name')->get();

        return view('livewire.admin.setting.product-subtype-manager', [
            'productSubtypes' => $productSubtypes,
        ]);
    }
}
