<?php

namespace App\Livewire\Admin\Setting;

use App\Imports\LeadsImport;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\ImportExcelCompleted;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Masmerise\Toaster\Toaster;

class LeadImport extends Component
{
    use WithFileUploads;

    #[Validate(['required', 'file', 'mimes:xlsx,xls'])]
    public $file = null;
    public bool $queued = false;

    /**
     * Handle the file upload and import.
     */
    public function updatedFile()
    {
        if ($this->file) {
            $this->importLeads();
        }
    }

    /**
     * Import the leads from the uploaded file.
     */
    public function importLeads()
    {
        Gate::authorize('importLead', Customer::class);

        $import = new LeadsImport;
        $users = User::role('superadmin')->get();

        Excel::queueImport($import, $this->file)
            ->chain([
                function () use ($import, $users) {
                    Toaster::success('Import completato!');

                    // invio notifica
                    Notification::send($users, new ImportExcelCompleted('leads'));
                }
            ]);

        $this->queued = true;
    }

    public function render()
    {
        return view('livewire.admin.setting.lead-import');
    }
}
