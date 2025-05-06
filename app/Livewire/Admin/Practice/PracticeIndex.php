<?php

namespace App\Livewire\Admin\Practice;

use App\Models\Practice;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class PracticeIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public ?ProductType $type = null;
    public ?bool $expired = false;

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
