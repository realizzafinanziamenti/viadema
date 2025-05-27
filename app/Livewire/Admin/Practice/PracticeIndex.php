<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use App\Models\ProductType;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class PracticeIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions, EnumHelper, InteractsWithDropdowns;

    public ?ProductType $type = null;
    public ?bool $expired = false;
    public Practice|null $selectedPractice = null;
    public string $search = '';
    public array $practiceStatuses = [];
    public int|null $selectedPracticeStatus = null;
    public string $startDate = '';
    public string $endDate = '';
    public array $orderBySelect = [];

    /**
     * This method is called when the user clicks the update status button.
     * It sets the selected practice and opens the modal for updating the status.
     */
    public function selectPracticeForStatus(int $id)
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Practice::class,
            property: 'selectedPractice',
            modalName: 'update-practice-status',
            notFoundMessage: 'Pratica non trovata'
        );
        $this->setPracticeStatus($this->selectedPractice->practice_status?->value);
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
        Gate::authorize('update', $this->selectedPractice);

        try {
            $this->selectedPractice->update(['practice_status' => $this->selectedPracticeStatus]);
            Toaster::success('Stato della pratica aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento dello stato della pratica');
        }

        $this->selectedPractice = null;
        $this->dispatch('close-modal', 'update-practice-status');
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected practice to be deleted.
     */
    public function selectPracticeForDelete(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Practice::class,
            property: 'selectedPractice',
            modalName: 'delete-practice',
            notFoundMessage: 'Pratica non trovata'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected practice and resets the selected practice to null.
     */
    public function deletePractice(): void
    {
        Gate::authorize('delete', $this->selectedPractice);

        $this->deleteSelectedEntity(
            property: 'selectedPractice',
            modalName: 'delete-practice',
            successMessage: 'Pratica eliminata con successo'
        );
    }

    /**
     * Updated search bar callback function
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * This method is called when the component is mounted.
     * It initializes the selects.
     */
    protected function initializeSelects(): void
    {
        $this->practiceStatuses = $this->getEnumOptions(PracticeStatus::class);
    }

    /**
     * This method is called when the user clicks the filter button.
     * It resets the page and closes the filter modal.
     */
    public function filter(): void
    {
        $this->resetPage();
        $this->dispatch('close-modal', 'filter-modal');
    }

    /**
     * This method is called when the user clicks the reset button.
     * It resets the filters and closes the filter modal.
     */
    public function resetFilter(): void
    {
        $this->reset([
            'selectedPracticeStatus',
            'startDate',
            'endDate',
        ]);

        $this->dispatch('close-modal', 'filter-modal');
    }

    public function applyFilters($query)
    {
        // if ($this->selectedPracticeStatus) {
        //     $query->where('practice_status', $this->selectedPracticeStatus);
        // }

        if ($this->startDate) {
            $query->whereDate('started_at', '>=', $this->startDate);
        };

        if ($this->endDate) {
            $query->whereDate('started_at', '<=', $this->endDate);
        };

        return $query;
    }

    public function mount(Request $request): void
    {
        Gate::authorize('viewAny', Practice::class);

        $slug = $request->route('slug');

        $this->type = $slug
            ? ProductType::where('slug', $slug)->firstOrFail()
            : null;

        $this->expired = $request->boolean('expired');
        $this->initializeSelects();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Practice::with('customer', 'teamMember', 'productType')
            ->filterByProductType($this->type)
            ->isExpired($this->expired)
            ->orderByDesc('updated_at');

        $query = $query->filterBySearch($this->search);
        $query = $this->applyFilters($query);
        $practices = $query->paginate(15);

        return view('livewire.admin.practice.practice-index', [
            'practices' => $practices,
            'productType' => $this->type,
            'expired' => $this->expired,
        ]);
    }
}
