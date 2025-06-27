<?php

namespace App\Livewire\Admin\Setting;

use App\Models\FinancialTable;
use App\Rules\NotUsedInPractices;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class FinancialTableManager extends Component
{
    use HandlesEntityActions, WithPagination, WithoutUrlPagination;

    public ?FinancialTable $selectedFinancialTable = null;
    public ?float $percentage = null;

    protected function validationAttributes(): array
    {
        return [
            'percentage' => 'percentuale',
        ];
    }

    /**
     * This method is called when the user clicks the create button.
     * It resets name, then opens the modal for creating a new financial.
     */
    public function openCreateFinancialTableModal(): void
    {
        $this->reset(['percentage', 'selectedFinancialTable']);
        $this->resetValidation();
        $this->dispatch('open-modal', 'create-financial-table');
    }

    /**
     * This method is called when the user clicks the create button in the modal.
     * It creates a new financial table and resets the value to null.
     */
    public function createFinancialTable(): void
    {
        Gate::authorize('create', FinancialTable::class);

        $this->validate(['percentage' => ['required', 'numeric', 'between:0,100', Rule::unique('financial_tables', 'percentage')]]);

        try {
            FinancialTable::create(['percentage' => $this->percentage]);
            Toaster::success('Provvigione creata con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante la creazione della provvigione: ' . $e->getMessage());
        }

        $this->reset('percentage');
        $this->dispatch('close-modal', 'create-financial-table');
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected financial table to be deleted.
     */
    public function selectFinancialTableForDelete(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: FinancialTable::class,
            property: 'selectedFinancialTable',
            modalName: 'delete-financial-table',
            notFoundMessage: 'Provvigione non trovata'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected financial table and resets the selected financial table to null.
     */
    public function deleteFinancialTable(): void
    {
        Gate::authorize('delete', $this->selectedFinancialTable);

        $this->deleteSelectedEntity(
            property: 'selectedFinancialTable',
            modalName: 'delete-financial-table',
            successMessage: 'Provvigione eliminata con successo'
        );

        $this->reset('selectedFinancialTable');
        $this->resetPage();
    }

    public function render()
    {
        $financialTables = FinancialTable::orderBy('percentage')->paginate(10);

        return view('livewire.admin.setting.financial-table-manager', [
            'financialTables' => $financialTables,
        ]);
    }
}
