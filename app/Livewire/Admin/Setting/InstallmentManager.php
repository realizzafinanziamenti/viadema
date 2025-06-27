<?php

namespace App\Livewire\Admin\Setting;

use App\Models\Installment;
use App\Rules\NotUsedInPractices;
use App\Rules\UniqueNormalized;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class InstallmentManager extends Component
{
    use HandlesEntityActions, WithPagination, WithoutUrlPagination;

    public ?Installment $selectedInstallment = null;
    public ?int $value = null;

    protected function validationAttributes(): array
    {
        return [
            'value' => 'valore',
        ];
    }

    /**
     * This method is called when the user clicks the create button.
     * It resets name, then opens the modal for creating a new installment.
     */
    public function openCreateInstallmentModal(): void
    {
        $this->reset(['value', 'selectedInstallment']);
        $this->resetValidation();
        $this->dispatch('open-modal', 'create-installment');
    }

    /**
     * This method is called when the user clicks the create button in the modal.
     * It creates a new installment and resets the name to null.
     */
    public function createInstallment(): void
    {
        Gate::authorize('create', Installment::class);

        $this->validate(['value' => ['required', 'integer', 'min:0', 'max:10000', new UniqueNormalized('installments', 'value')]]);

        try {
            Installment::create(['value' => $this->value]);
            Toaster::success('Rata creata con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante la creazione della rata: ' . $e->getMessage());
        }

        $this->reset('value');
        $this->dispatch('close-modal', 'create-installment');
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected installment to be deleted.
     */
    public function selectInstallmentForDelete(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Installment::class,
            property: 'selectedInstallment',
            modalName: 'delete-installment',
            notFoundMessage: 'Rata non trovata'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected installment and resets the selected installment to null.
     */
    public function deleteInstallment(): void
    {
        Gate::authorize('delete', $this->selectedInstallment);

        $this->deleteSelectedEntity(
            property: 'selectedInstallment',
            modalName: 'delete-installment',
            successMessage: 'Rata eliminata con successo'
        );

        $this->reset('selectedInstallment');
        $this->resetPage();
    }

    public function render()
    {
        $installments = Installment::orderBy('value')->paginate(10);

        return view('livewire.admin.setting.installment-manager', [
            'installments' => $installments,
        ]);
    }
}
