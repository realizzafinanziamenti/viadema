<?php

namespace App\Livewire\Admin\Lead;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class LeadIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Customer::with('user')
            ->leads()
            ->orderByDesc('updated_at');

        // $query = $query->filterBySearch($this->search);
        $leads = $query->paginate(15);

        return view('livewire.admin.lead.lead-index', [
            'leads' => $leads,
        ]);
    }
}
