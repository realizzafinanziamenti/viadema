<?php

namespace App\Livewire\Admin\FormDocument;

use Livewire\Attributes\Layout;
use Livewire\Component;

class FormDocumentIndex extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.form-document.form-document-index');
    }
}
