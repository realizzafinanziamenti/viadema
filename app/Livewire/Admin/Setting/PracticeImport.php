<?php

namespace App\Livewire\Admin\Setting;

use App\Models\Practice;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

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

    public function import()
    {
        $this->isImporting = true;

        // Handle the file import logic here
    }

    public function mount()
    {
        Gate::authorize('import practice from file', Practice::class);
    }

    public function render()
    {
        return view('livewire.admin.setting.practice-import');
    }
}
