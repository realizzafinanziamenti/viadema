<?php

namespace App\Livewire\Admin\Setting;

use Livewire\Attributes\Layout;
use Livewire\Component;

class SettingIndex extends Component
{
    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.setting.setting-index');
    }
}
