<?php

namespace App\Livewire\Admin\Dashboard;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PracticeCounter extends Component
{
    public int $praticeCount = 0;
    public int $approvedPracticeCount = 0;  // deliberate
    public int $pendingPracticeCount = 0;  // in attesa
    public int $underReviewPracticeCount = 0;  // nuove

    /**
     * Get the total count of all practices.
     */
    public function getTotalPartialCount(): int
    {
        return $this->approvedPracticeCount + $this->pendingPracticeCount + $this->underReviewPracticeCount;
    }

    #[Computed]
    public function approvedPercentage(): float
    {
        $total = $this->getTotalPartialCount();
        return $total > 0 ? round(($this->approvedPracticeCount / $total) * 100) : 0;
    }

    #[Computed]
    public function pendingPercentage(): float
    {
        $total = $this->getTotalPartialCount();
        return $total > 0 ? round(($this->pendingPracticeCount / $total) * 100) : 0;
    }

    #[Computed]
    public function underReviewPercentage(): float
    {
        return max(0, 100 - $this->approvedPercentage - $this->pendingPercentage);
    }

    public function mount()
    {
        $this->praticeCount = Practice::count();
        $this->approvedPracticeCount = Practice::where('practice_status', PracticeStatus::APPROVED->value)->count();
        $this->pendingPracticeCount = Practice::where('practice_status', PracticeStatus::PENDING->value)->count();
        $this->underReviewPracticeCount = Practice::where('practice_status', PracticeStatus::UNDER_REVIEW->value)->count();
    }

    public function render()
    {
        return view('livewire.admin.dashboard.practice-counter');
    }
}
