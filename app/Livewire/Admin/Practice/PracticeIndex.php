<?php

namespace App\Livewire\Admin\Practice;

use App\Models\Practice;
use App\Models\ProductType;
use Exception;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class PracticeIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public ?ProductType $type = null;
    public ?bool $expired = false;
    public $selectedPractice = null;
    public $search = '';

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected practice to be deleted.
     */
    public function selectPracticeForDelete(int $practiceId)
    {
        try {
            $this->selectedPractice = Practice::findOrFail($practiceId);
        } catch (Exception $e) {
            Toaster::error('Pratica non trovato');
            return;
        }

        $this->dispatch('open-modal', 'delete-practice');
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected practice and resets the selected practice to null.
     */
    public function deletePractice()
    {
        if ($this->selectedPractice) {
            // Gate::authorize('delete', $this->selectedPractice);

            $this->selectedPractice->delete();
            $this->selectedPractice = null;

            $this->dispatch('close-modal', 'delete-practice');
            Toaster::success('Pratica eliminata con successo');
        }
    }

    /**
     * Updated search bar callback function
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function mount(Request $request): void
    {
        $slug = $request->route('slug');

        $this->type = $slug
            ? ProductType::where('slug', $slug)->firstOrFail()
            : null;

        $this->expired = $request->boolean('expired');
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Practice::with('customer', 'teamMember')
            ->filterByProductType($this->type)
            ->isExpired($this->expired);

        $practices = $query->paginate(15);

        return view('livewire.admin.practice.practice-index', [
            'practices' => $practices,
            'productType' => $this->type,
            'expired' => $this->expired,
        ]);
    }
}
