<?php

namespace App\Livewire\Admin\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class CustomerIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $search = '';

    public function mount()
    {
        Gate::authorize('viewAny', Customer::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = Customer::with('user')
            ->orderByDesc('updated_at');

        $customers = $query->paginate(15);

        return view('livewire.admin.customer.customer-index', [
            'customers' => $customers,
        ]);
    }
}
