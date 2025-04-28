<?php

namespace App\Livewire\Admin\Team;

use App\Models\User;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class TeamIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $selectedUser = null;

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected user to be deleted.
     */
    public function selectUserForDelete(int $userId)
    {
        try {
            $this->selectedUser = User::findOrFail($userId);
        } catch (Exception $e) {
            Toaster::error('Collaboratore non trovato');
            return;
        }

        $this->modal('delete-user')->show();
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected user and resets the selected user to null.
     */
    public function deleteUser()
    {
        if ($this->selectedUser) {
            $this->selectedUser->delete();
            $this->selectedUser = null;

            $this->modal('delete-user')->close();
            Toaster::success('Collaboratore eliminato con successo');
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = User::teamMembers()
            ->orderByDesc('updated_at');
        $teamMembers = $query->paginate(15);

        return view('livewire.admin.team.team-index', [
            'teamMembers' => $teamMembers,
        ]);
    }
}
