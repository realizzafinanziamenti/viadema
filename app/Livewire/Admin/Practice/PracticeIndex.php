<?php

namespace App\Livewire\Admin\Practice;

use App\Models\Practice;
use App\Models\ProductType;
use App\Traits\HandlesDeletions;
use Exception;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class PracticeIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesDeletions;

    public ?ProductType $type = null;
    public ?bool $expired = false;
    public Practice|null $selectedPractice = null;
    public $search = '';

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected practice to be deleted.
     */
    public function selectPracticeForDelete(int $id): void
    {
        $this->selectEntityForDelete(
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
        // Gate::authorize('delete', $this->selectedPractice);

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
