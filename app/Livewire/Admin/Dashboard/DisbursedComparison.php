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

    /**
     * Get date ranges for the current month, divided into steps of 5 days.
     */
    protected function getDateRanges(Carbon $date, int $step = 5): array
    {
        $ranges = [];

        $daysInMonth = $date->daysInMonth;
        $day = 1;

        while ($day <= $daysInMonth) {
            $start = $date->copy()->startOfMonth()->addDays($day - 1)->startOfDay();

            $endDay = $day + $step - 1;
            if ($endDay > $daysInMonth) {
                $endDay = $daysInMonth;
            }

            $end = $date->copy()->startOfMonth()->addDays($endDay - 1)->endOfDay();

            $ranges[] = [
                'label' => $endDay . 'gg',
                'start' => $start,
                'end' => $end,
            ];

            $day += $step;
        }

        return $ranges;
    }

    /**
     * Get the total disbursed amounts for each range in the current month.
     */
    protected function getDisbursedByRanges(Carbon $monthDate): array
    {
        $ranges = $this->getDateRanges($monthDate);

        return collect($ranges)->map(function ($range) {
            $total = Practice::where('practice_status', PracticeStatus::DISBURSED->value)
                ->whereBetween('disbursement_date', [$range['start'], $range['end']])
                ->sum('amount_disbursed');

            return [
                'label' => $range['label'],
                'total' => $total,
            ];
        })->toArray();
    }

    #[Computed]
    public function currentMonthDisbursedByRange(): array
    {
        return $this->getDisbursedByRanges($this->now);
    }

    #[Computed]
    public function lastMonthDisbursedByRange(): array
    {
        return $this->getDisbursedByRanges($this->lastMonth);
    }

    #[Computed]
    public function disbursedChartLabels(): array
    {
        return array_column($this->getDateRanges($this->now), 'label');
    }

    #[Computed]
    public function disbursedChartCurrentValues(): array
    {
        return array_column($this->currentMonthDisbursedByRange, 'total');
    }

    #[Computed]
    public function disbursedChartLastValues(): array
    {
        return array_column($this->lastMonthDisbursedByRange, 'total');
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
