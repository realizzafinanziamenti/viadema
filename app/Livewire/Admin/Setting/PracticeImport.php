<?php

namespace App\Livewire\Admin\Setting;

use App\Imports\PracticesImport;
use App\Models\Practice;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class PracticeImport extends Component
{
    use WithFileUploads;

    #[Validate(['required', 'file', 'mimes:xlsx,xls'])]
    public $file = null;
    public bool $isImporting = false;

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
        $this->isImporting = true;

        Excel::import(new PracticesImport, $this->file);
    }

    public function mount()
    {
        Gate::authorize('import practices', Practice::class);
    }

    public function render()
    {
        return view('livewire.admin.setting.practice-import');
    }
}
