<?php

namespace App\Livewire\Admin\Setting;

use App\Models\Insurance;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class InsuranceManager extends Component
{
    use HandlesEntityActions, WithPagination, WithoutUrlPagination;

    public ?Insurance $selectedInsurance = null;
    public ?string $name = null;

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
        ];
    }

    /**
     * This method is called when the user clicks the create button.
     * It resets name, then opens the modal for creating a new insurance.
     */
    public function openCreateInsuranceModal(): void
    {
        $this->reset(['name', 'selectedInsurance']);
        $this->resetValidation();
        $this->dispatch('open-modal', 'create-insurance');
    }

    /**
     * This method is called when the user clicks the create button in the modal.
     * It creates a new insurance and resets the name to null.
     */
    public function createInsurance(): void
    {
        Gate::authorize('create', Insurance::class);

        $this->validate(['name' => ['required', 'string', 'max:255', Rule::unique('insurances', 'name')]]);

        try {
            Insurance::create(['name' => $this->name]);
            Toaster::success('Assicurazione creata con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante la creazione dell\'assicurazione: ' . $e->getMessage());
        }

        $this->reset('name');
        $this->dispatch('close-modal', 'create-insurance');
    }

    // UPDATE DISABLED
    /**
     * This method is called when the user clicks the update button.
     * It sets the selected insurance and opens the modal for updating the name.
     */
    // public function selectInsuranceForUpdate(int $id)
    // {
    //     $this->resetValidation();
    //     $this->selectEntityForAction(
    //         id: $id,
    //         modelClass: Insurance::class,
    //         property: 'selectedInsurance',
    //         modalName: 'update-insurance',
    //         notFoundMessage: 'Assicurazione non trovata'
    //     );
    //     $this->name = $this->selectedInsurance->name;
    // }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the insurance and resets the name to null.
     */
    // public function updateInsurance(): void
    // {
    //     Gate::authorize('update', $this->selectedInsurance);

    //     $this->validate(['name' => ['required', 'string', 'max:255', Rule::unique('insurances', 'name')->ignore($this->selectedInsurance?->id)]]);

    //     try {
    //         $this->selectedInsurance->update(['name' => $this->name]);
    //         Toaster::success('Assicurazione aggiornata con successo');
    //     } catch (Exception $e) {
    //         Toaster::error('Errore durante l\'aggiornamento dell\' assicurazione: ' . $e->getMessage());
    //     }

    //     $this->reset(['name', 'selectedInsurance']);
    //     $this->dispatch('close-modal', 'update-insurance');
    // }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected insurance to be deleted.
     */
    public function selectInsuranceForDelete(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Insurance::class,
            property: 'selectedInsurance',
            modalName: 'delete-insurance',
            notFoundMessage: 'Assicurazione non trovata'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected insurance and resets the selected insurance to null.
     */
    public function deleteInsurance(): void
    {
        Gate::authorize('delete', $this->selectedInsurance);

        $this->deleteSelectedEntity(
            property: 'selectedInsurance',
            modalName: 'delete-insurance',
            successMessage: 'Assicurazione eliminata con successo'
        );

        $this->reset('selectedInsurance');
        $this->resetPage();
    }

    public function render()
    {
        $insurances = Insurance::orderBy('name')->paginate(10);

        return view('livewire.admin.setting.insurance-manager', [
            'insurances' => $insurances,
        ]);
    }
}
