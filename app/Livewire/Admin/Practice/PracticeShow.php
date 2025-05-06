<?php

namespace App\Livewire\Admin\Practice;

use App\Models\Practice;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PracticeShow extends Component
{
    public Practice $practice;

    public function mount($id)
    {
        $this->practice = Practice::findOrFail($id);
        Gate::authorize('view', $this->practice);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.practice.practice-show');
    }
}
