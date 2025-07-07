<?php

namespace App\Livewire\Admin\Setting;

use App\Imports\PracticesImport;
use App\Models\Practice;
use App\Models\User;
use App\Notifications\PracticesImportCompleted;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Masmerise\Toaster\Toaster;

class PracticeImport extends Component
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
            $this->import();
        }
    }

    /**
     * Import the practices from the uploaded file.
     */
    public function import()
    {
        Gate::authorize('importPractice', Practice::class);

        $import = new PracticesImport;
        $users = User::role('superadmin')->get();

        Excel::queueImport($import, $this->file)
            ->chain([
                function () use ($import, $users) {
                    Toaster::success('Import completato!');

                    // invio notifica
                    Notification::send($users, new PracticesImportCompleted);
                }
            ]);

        $this->queued = true;
    }

    public function render()
    {
        return view('livewire.admin.setting.practice-import');
    }
}
