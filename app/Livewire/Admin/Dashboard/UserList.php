<?php

namespace App\Livewire\Admin\Dashboard;

use App\Enums\PracticeStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class UserList extends Component
{
    public  $users = null;

    public function mount()
    {
        $this->users = User::with('profile')
            ->withCount(['practices as disbursed_practices_count' => function ($query) {
                $query->where('practice_status', PracticeStatus::DISBURSED);
            }])
            ->withoutSuperadmin()
            ->orderByDesc('updated_at')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard.user-list');
    }
}
