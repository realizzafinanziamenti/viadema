<?php

namespace App\Livewire\Admin\ActivityLog;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public ?Activity $selectedLog = null;
    public int $paginate = 15;

    /**
     * Select a log to view its details and open the modal.
     */
    public function selectLogForDetails(int $logId)
    {
        $this->selectedLog = Activity::find($logId);
        Gate::authorize('view', $this->selectedLog);
        $this->dispatch('open-modal', 'log-details');
    }

    /**
     * Format field value for display
     */
    public function formatFieldValue($value): string
    {
        if (is_null($value)) {
            return '-';
        }

        return match ($value) {
            // Events
            'created' => 'Creazione',
            'updated' => 'Modifica',
            'deleted' => 'Eliminazione',
            'restored' => 'Ripristino',

            // Log names
            'user' => 'Collaboratori',
            'customer' => 'Clienti',
            'practice' => 'Pratiche',
            'import_success' => 'Import completato',
            'import_failure' => 'Import fallito',
            'import_validation_failure' => 'Import fallito',

            // User fields
            'notifications_enabled' => $value ? 'Abilitate' : 'Disabilitate',
            'profile_photo_path' => $value ? 'Presente' : 'Non impostata',

            default => ucfirst(str_replace('_', ' ', $value))
        };
    }

    /**
     * Get the activity logs with pagination.
     */
    protected function getActivityLogs()
    {
        return Activity::with('causer')
            ->latest()
            ->paginate($this->paginate);
    }

    public function mount()
    {
        Gate::authorize('viewAny', Activity::class);
    }

    public function render()
    {
        return view('livewire.admin.activity-log.activity-log-index', [
            'activityLogs' => $this->getActivityLogs(),
        ]);
    }
}
