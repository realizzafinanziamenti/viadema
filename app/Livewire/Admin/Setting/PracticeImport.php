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

    public function import()
    {


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
