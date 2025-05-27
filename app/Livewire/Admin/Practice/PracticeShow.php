<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class PracticeShow extends Component
{
    use HandlesEntityActions, InteractsWithDropdowns, EnumHelper;

    public Practice $practice;
    public array $practiceStatuses = [];
    public int|null $selectedPracticeStatus = null;

    /**
     * This method is called when the user clicks the update status button.
     * It sets the selected practice and opens the modal for updating the status.
     */
    public function openUpdatePracticeStatusModal()
    {
        $this->dispatch('open-modal', 'update-practice-status');
    }

    /**
     * This method is called when the user selects a practice status from the dropdown.
     * It sets the selected practice status.
     */
    public function setPracticeStatus(?int $value = null): void
    {
        $this->setSelectValue('selectedPracticeStatus', $value);
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the practice status and resets the selected practice to null.
     */
    public function updatePracticeStatus(): void
    {
        Gate::authorize('update', $this->practice);

        try {
            $this->practice->update(['practice_status' => $this->selectedPracticeStatus]);
            Toaster::success('Stato della pratica aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento dello stato della pratica');
        }

        $this->dispatch('close-modal', 'update-practice-status');
    }

    /**
     * This method is called when the component is mounted.
     * It initializes the practice statuses select.
     */
    protected function initializePracticeStatuses(): void
    {
        $this->practiceStatuses = $this->getEnumOptions(PracticeStatus::class);
    }

    public function mount($id)
    {
        $this->practice = Practice::findOrFail($id);
        Gate::authorize('view', $this->practice);

        $this->initializePracticeStatuses();
        $this->setPracticeStatus($this->practice->practice_status?->value);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.practice.practice-show');
    }
}
