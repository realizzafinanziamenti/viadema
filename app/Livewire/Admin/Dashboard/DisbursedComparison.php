<?php

namespace App\Livewire\Admin\Dashboard;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DisbursedComparison extends Component
{
    public ?Carbon $now = null;
    public ?Carbon $lastMonth = null;
    public float $currentMonthDisbursed = 0.0;
    public float $lastMonthDisbursed = 0.0;

    protected function disbursedQueryForMonth(Carbon $monthDate): Builder
    {
        return Practice::query()
            ->filteredForDepartment()
            ->join(
                'practice_opportunities',
                'practice_opportunities.id',
                '=',
                'practices.practice_opportunity_id'
            )
            ->where('practices.practice_status', PracticeStatus::DISBURSED->value)
            ->whereMonth('practices.disbursement_date', $monthDate->month)
            ->whereYear('practices.disbursement_date', $monthDate->year);
    }

    /**
     * Set the disbursed amounts for the current and last month.
     */
    protected function setDisbursedAmounts(): void
    {
        $this->currentMonthDisbursed = (float) $this->disbursedQueryForMonth($this->now)
            ->sum('practice_opportunities.total_amount');

        $this->lastMonthDisbursed = (float) $this->disbursedQueryForMonth($this->lastMonth)
            ->sum('practice_opportunities.total_amount');
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
            $start = $date->copy()
                ->startOfMonth()
                ->addDays($day - 1)
                ->startOfDay();

            $endDay = $day + $step - 1;

            if ($endDay >= 30) {
                $endDay = $daysInMonth;
            } else {
                $endDay = min($endDay, $daysInMonth);
            }

            $end = $date->copy()
                ->startOfMonth()
                ->addDays($endDay - 1)
                ->endOfDay();

            $label = $endDay >= 30 ? '30gg' : $endDay . 'gg';

            $ranges[] = [
                'label' => $label,
                'start' => $start,
                'end' => $end,
            ];

            if ($endDay >= 30) {
                break;
            }

            $day += $step;
        }

        return $ranges;
    }

    /**
     * Get the total disbursed amounts for each range in the selected month.
     */
    protected function getDisbursedByRanges(Carbon $monthDate): array
    {
        $practices = $this->disbursedQueryForMonth($monthDate)
            ->get([
                'practices.disbursement_date',
                'practice_opportunities.total_amount as opportunity_total_amount',
            ]);

        $ranges = $this->getDateRanges($monthDate);

        return collect($ranges)->map(function ($range) use ($practices) {
            $total = $practices
                ->filter(fn ($practice) => $practice->disbursement_date?->between($range['start'], $range['end']))
                ->sum(fn ($practice) => (float) ($practice->opportunity_total_amount ?? 0));

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
        if (empty($this->lastMonthDisbursed)) {
            return $this->currentMonthDisbursed > 0 ? '+100%' : '0%';
        }

        $value = round((($this->currentMonthDisbursed - $this->lastMonthDisbursed) / $this->lastMonthDisbursed) * 100, 2);

        return ($value > 0 ? '+' : '') . $value . '%';
    }

    public function mount()
    {
        Gate::authorize('view disbursed comparison');

        $this->now = now();
        $this->lastMonth = now()->subMonth();

        $this->setDisbursedAmounts();
    }

    public function render()
    {
        return view('livewire.admin.dashboard.disbursed-comparison');
    }
}
