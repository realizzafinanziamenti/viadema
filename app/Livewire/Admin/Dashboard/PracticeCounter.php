<?php

namespace App\Livewire\Admin\Dashboard;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PracticeCounter extends Component
{
    public int $practiceCount = 0;
    public string $approvedStatus = PracticeStatus::APPROVED->value;
    public string $disbursedStatus = PracticeStatus::DISBURSED->value;
    public string $underReviewStatus = PracticeStatus::UNDER_REVIEW->value;
    public int $approvedPracticeCount = 0;  // deliberate
    public int $disbursedPracticeCount = 0;  // liquidate
    public int $underReviewPracticeCount = 0;  // istruttoria

    /**
     * Get the total count of all practices.
     */
    public function getTotalPartialCount(): int
    {
        return $this->approvedPracticeCount + $this->disbursedPracticeCount + $this->underReviewPracticeCount;
    }

    #[Computed]
    public function approvedPercentage(): float
    {
        $total = $this->getTotalPartialCount();
        return $total > 0 ? round(($this->approvedPracticeCount / $total) * 100) : 0;
    }

    #[Computed]
    public function disbursedPercentage(): float
    {
        $total = $this->getTotalPartialCount();
        return $total > 0 ? round(($this->disbursedPracticeCount / $total) * 100) : 0;
    }

    #[Computed]
    public function underReviewPercentage(): float
    {
        return max(0, 100 - $this->approvedPercentage - $this->disbursedPercentage);
    }

    public function mount()
    {
        Gate::authorize('view practice counters');

        $result = Practice::filteredForDepartment()
            ->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN practice_status = ? THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN practice_status = ? THEN 1 ELSE 0 END) as disbursed_count,
            SUM(CASE WHEN practice_status = ? THEN 1 ELSE 0 END) as under_review_count
        ", [
                $this->approvedStatus,
                $this->disbursedStatus,
                $this->underReviewStatus,
            ])->first();
        $this->practiceCount = $result->total;
        $this->approvedPracticeCount = $result->approved_count ?? 0;
        $this->disbursedPracticeCount = $result->disbursed_count ?? 0;
        $this->underReviewPracticeCount = $result->under_review_count ?? 0;
    }

    public function render()
    {
        return view('livewire.admin.dashboard.practice-counter');
    }
}
