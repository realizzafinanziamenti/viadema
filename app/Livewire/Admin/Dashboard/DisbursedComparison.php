<?php

namespace App\Livewire\Admin\Dashboard;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DisbursedComparison extends Component
{
    public ?Carbon $now = null;
    public ?Carbon $lastMonth = null;
    public float $currentMonthDisbursed = 0.0;
    public float $lastMonthDisbursed = 0.0;

    /**
     * Set the disbursed amounts for the current and last month.
     */
    protected function setDisbursedAmounts(): void
    {
        $this->currentMonthDisbursed = Practice::where('practice_status', PracticeStatus::DISBURSED->value)
            ->whereMonth('disbursement_date', $this->now->month)
            ->whereYear('disbursement_date', $this->now->year)
            ->sum('amount_disbursed');

        $this->lastMonthDisbursed = Practice::where('practice_status', PracticeStatus::DISBURSED->value)
            ->whereMonth('disbursement_date', $this->lastMonth->month)
            ->whereYear('disbursement_date', $this->lastMonth->year)
            ->sum('amount_disbursed');
    }

    #[Computed]
    public function currentMonthName(): string
    {
        return ucfirst($this->now->locale('it')->translatedFormat('F'));
    }

    #[Computed]
    public function lastMonthName(): string
    {
        return ucfirst($this->lastMonth->locale('it')->translatedFormat('F'));
    }

    #[Computed]
    public function currentMonthDisbursedFormatted(): string
    {
        return '€' . number_format($this->currentMonthDisbursed, 2, ',', '.');
    }

    #[Computed]
    public function percentageComparison(): string
    {
        // If there was no disbursement last month, we consider the current month's disbursement as a 100% increase.
        if (empty($this->lastMonthDisbursed)) {
            return $this->currentMonthDisbursed > 0 ? '+100%' : '0%';
        }

        $value = round((($this->currentMonthDisbursed - $this->lastMonthDisbursed) / $this->lastMonthDisbursed) * 100, 2);
        return ($value > 0 ? '+' : '') . $value . '%';
    }

    public function mount()
    {
        $this->now = now();
        $this->lastMonth = now()->subMonth();

        $this->setDisbursedAmounts();
    }

    public function render()
    {
        return view('livewire.admin.dashboard.disbursed-comparison');
    }
}
