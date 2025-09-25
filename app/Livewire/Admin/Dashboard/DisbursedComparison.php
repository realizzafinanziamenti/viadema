<?php

namespace App\Livewire\Admin\Dashboard;

use App\Enums\PracticeStatus;
use App\Models\Practice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
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
        $this->currentMonthDisbursed = Practice::filteredForDepartment()
            ->where('practice_status', PracticeStatus::DISBURSED->value)
            ->whereMonth('disbursement_date', $this->now->month)
            ->whereYear('disbursement_date', $this->now->year)
            ->sum('amount_disbursed');

        $this->lastMonthDisbursed = Practice::filteredForDepartment()
            ->where('practice_status', PracticeStatus::DISBURSED->value)
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

        // Retrieve the number of days in the month
        // Ottieni il numero di giorni nel mese
        $daysInMonth = $date->daysInMonth;
        $day = 1;

        // Loop to generate day ranges (e.g. 1–5, 6–10, ...)
        // Cicla per generare intervalli di giorni (es. 1–5, 6–10, ...)
        while ($day <= $daysInMonth) {
            // Calculate the start day of the current range
            // Calcola il giorno di inizio dell'intervallo corrente
            $start = $date->copy()->startOfMonth()->addDays($day - 1)->startOfDay();

            // Calculate the end day of the current range
            // Calcola il giorno di fine dell'intervallo corrente
            $endDay = $day + $step - 1;
            if ($endDay > $daysInMonth) {
                // Prevent going past the end of the month
                // Evita di superare la fine del mese
                $endDay = $daysInMonth;
            }

            // Calculate the end date for the current range
            // Calcola la data di fine per l'intervallo corrente
            $end = $date->copy()->startOfMonth()->addDays($endDay - 1)->endOfDay();

            // Add the range to the list with a label like "5gg", "10gg", etc.
            // Aggiungi l'intervallo alla lista con un'etichetta come "5gg", "10gg", ecc.
            $ranges[] = [
                'label' => $endDay . 'gg',
                'start' => $start,
                'end' => $end,
            ];

            // Move to the next range block
            // Passa al blocco di intervallo successivo
            $day += $step;
        }

        return $ranges;
    }

    /**
     * Get the total disbursed amounts for each range in the current month.
     */
    protected function getDisbursedByRanges(Carbon $monthDate): array
    {
        // Fetch all disbursed practices for that month with only necessary fields
        // Recupera tutte le pratiche disbursate per quel mese con solo i campi necessari
        $practices = Practice::filteredForDepartment()
            ->where('practice_status', PracticeStatus::DISBURSED->value)
            ->whereMonth('disbursement_date', $monthDate->month)
            ->whereYear('disbursement_date', $monthDate->year)
            ->get(['disbursement_date', 'amount_disbursed']);

        // Get the list of day ranges for that month
        // Ottieni gli intervalli di date per quel mese
        $ranges = $this->getDateRanges($monthDate);

        // For each range, calculate the total disbursed amount
        // Per ogni intervallo calcola il totale dei disbursamenti
        return collect($ranges)->map(function ($range) use ($practices) {
            $total = $practices
                ->filter(fn($p) => $p->disbursement_date->between($range['start'], $range['end']))
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
