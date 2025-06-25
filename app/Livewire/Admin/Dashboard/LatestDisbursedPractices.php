<?php

namespace App\Livewire\Admin\Dashboard;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use Livewire\Component;

class LatestDisbursedPractices extends Component
{
    public $practices = null;

    public function mount()
    {
        $this->practices = Practice::with(['user'])
            ->where('practice_status', PracticeStatus::DISBURSED)
            ->latest('updated_at')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard.latest-disbursed-practices');
    }
}
