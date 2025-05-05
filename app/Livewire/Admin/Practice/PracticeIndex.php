<?php

namespace App\Livewire\Admin\Practice;

use App\Models\Practice;
use App\Models\ProductType;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PracticeIndex extends Component
{
    public ?ProductType $type = null;

    public function mount(?string $slug = null): void
    {
        $this->type = $slug
            ? ProductType::where('slug', $slug)->firstOrFail()
            : null;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Practice::with('customer', 'teamMember')
            ->filterByProductType($this->type);

        $practices = $query->paginate(15);

        return view('livewire.admin.practice.practice-index', [
            'practices' => $practices,
            'productType' => $this->type,
        ]);
    }
}
