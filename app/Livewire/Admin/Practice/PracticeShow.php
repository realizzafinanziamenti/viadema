<?php

namespace App\Livewire\Admin\Practice;

use App\Enums\PracticeStatus;
use App\Models\Attachment;
use App\Models\Practice;
use App\Traits\EnumHelper;
use App\Traits\HandlesEntityActions;
use App\Traits\InteractsWithDropdowns;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PracticeShow extends Component
{
    use HandlesEntityActions, InteractsWithDropdowns, EnumHelper;

    public Practice $practice;
    public array $practiceStatuses = [];
    public ?string $selectedPracticeStatus = null;
    public ?Attachment $selectedAttachment = null;

    /**
     * This method is called when the user clicks the update status button.
     * It sets the selected practice and opens the modal for updating the status.
     */
    public function openUpdatePracticeStatusModal()
    {
        $this->dispatch('open-modal', 'update-practice-status');
    }

    /**
     * This method is called when the user selects a practice status from the dropdown.
     * It sets the selected practice status.
     */
    public function setPracticeStatus(?string $value = null): void
    {
        $this->setSelectValue('selectedPracticeStatus', $value);
    }

    /**
     * This method is called when the user clicks the update button in the modal.
     * It updates the practice status and resets the selected practice to null.
     */
    public function updatePracticeStatus(): void
    {
        Gate::authorize('update', $this->practice);

        try {
            $this->practice->update(['practice_status' => $this->selectedPracticeStatus]);
            Toaster::success('Stato della pratica aggiornato con successo');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'aggiornamento dello stato della pratica');
        }

        $this->dispatch('close-modal', 'update-practice-status');
    }

    /**
     * This method is called when the component is mounted.
     * It initializes the practice statuses select.
     */
    protected function initializePracticeStatuses(): void
    {
        $this->practiceStatuses = $this->getEnumOptions(PracticeStatus::class);
    }

    /**
     * This method is called when the user clicks the download button for an attachment.
     * It retrieves the attachment by ID and returns a download response.
     */
    public function download(int $id): ?StreamedResponse
    {
        Gate::authorize('view', $this->practice);

        try {
            $attachment = Attachment::findOrFail($id);
            return Storage::download($attachment->file_path, $attachment->file_name);
        } catch (Exception $e) {
            Toaster::error('File non trovato o errore: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected attachment to be deleted.
     */
    public function selectAttachmentForDelete(int $id): void
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: Attachment::class,
            property: 'selectedAttachment',
            modalName: 'delete-attachment',
            notFoundMessage: 'Allegato non trovato'
        );
    }

    /**
     * This method is called when the user confirms the deletion of an attachment.
     * It deletes the selected attachment and shows a success message.
     */
    public function deleteAttachment(): void
    {
        Gate::authorize('delete', $this->practice);

        try {
            DB::transaction(function () {
                Storage::disk('public')->delete($this->selectedAttachment->file_path);
                $this->selectedAttachment->delete();
            });

            Toaster::success('Allegato eliminato con successo');
            $this->dispatch('close-modal', 'delete-attachment');
        } catch (Exception $e) {
            Toaster::error('Errore durante l\'eliminazione dell\'allegato: ' . $e->getMessage());
        }
    }

    public function mount($id)
    {
        $this->practice = Practice::findOrFail($id);
        Gate::authorize('view', $this->practice);

        $this->initializePracticeStatuses();
        $this->setPracticeStatus($this->practice->practice_status?->value);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        return view('livewire.admin.practice.practice-show');
    }
}
