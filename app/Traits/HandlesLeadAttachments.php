<?php

namespace App\Traits;

use App\Models\Customer;
use App\Models\Attachment;
use App\Models\PracticeOpportunity;
use App\Services\PracticeAttachments;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Masmerise\Toaster\Toaster;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;

trait HandlesLeadAttachments
{
    use AcceptedFileTypes, \Livewire\WithFileUploads;

    public array $temporaryFiles = [];
    public array $attachments = [];
    public ?Attachment $selectedAttachment = null;

    #[Locked]
    public ?int $attachmentOpportunityId = null;

    private function attachmentOpportunity(): ?PracticeOpportunity
    {
        abort_unless($this->lead->fresh()?->isLead(), 404);

        return $this->attachmentOpportunityId === null ? null
            : $this->lead->practiceOpportunities()->findOrFail($this->attachmentOpportunityId);
    }

    private function validateAttachments(string $field): void
    {
        $this->validate([
            $field => ['nullable', 'array', 'max:10'],
            $field.'.*' => ['nullable', 'file', 'mimetypes:'.implode(',', $this->acceptedFileTypesArray()), 'max:10240'],
        ], [
            $field.'.max' => 'Puoi caricare al massimo 10 file.',
            $field.'.*.max' => 'Ogni file non puÃ² superare i 10MB.',
            $field.'.*.mimetypes' => 'Formato file non valido.',
        ]);
    }

    public function updatedTemporaryFiles(): void
    {
        $this->authorizeAttachmentUpload();
        $this->validateAttachments('temporaryFiles');
        $this->attachments = array_merge($this->attachments, $this->temporaryFiles);
        $this->temporaryFiles = [];
        $this->validateAttachments('attachments');
    }

    public function deleteTemporaryFile(int $index): void
    {
        $this->authorizeAttachmentUpload();
        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function download(int $id): ?StreamedResponse
    {
        Gate::authorize('view', $this->lead->fresh());
        $opportunity = $this->attachmentOpportunity();
        abort_unless($opportunity, 404);
        $attachment = $opportunity->attachments()->findOrFail($id);

        try {
            return app(PracticeAttachments::class)->download($attachment);
        } catch (FileNotFoundException $exception) {
            Toaster::error('File non trovato. Contatta un amministratore.');
        } catch (Exception $exception) {
            report($exception);
            Toaster::error('Errore di accesso al filesystem. Riprova o contatta un amministratore.');
        }

        return null;
    }

    public function selectAttachmentForDelete(int $id): void
    {
        Gate::authorize('delete', $this->lead->fresh());
        $opportunity = $this->attachmentOpportunity();
        abort_unless($opportunity, 404);
        $this->selectedAttachment = $opportunity->attachments()->findOrFail($id);
        $this->dispatch('open-modal', 'delete-attachment');
    }

    public function deleteAttachment(): void
    {
        try {
            DB::transaction(function (): void {
                $lead = Customer::lockForUpdate()->findOrFail($this->lead->id);
                Gate::authorize('delete', $lead);
                $opportunity = $this->attachmentOpportunity();
                abort_unless($opportunity, 404);
                $attachment = $opportunity->attachments()->findOrFail($this->selectedAttachment?->id);
                // Keep the file until commit, so a database failure cannot lose it.
                $attachment->delete();
                DB::afterCommit(fn () => Storage::disk('public')->delete($attachment->file_path));
            });
        } catch (AuthorizationException|ModelNotFoundException|HttpException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            report($exception);
            Toaster::error("Errore durante l'eliminazione dell'allegato.");
            return;
        }
        $this->selectedAttachment = null;
        $this->dispatch('close-modal', 'delete-attachment');
        Toaster::success('Allegato eliminato con successo');
    }

    private function authorizeAttachmentUpload(): void
    {
        if (isset($this->lead)) {
            Gate::authorize('update', $this->lead->fresh());
            $this->attachmentOpportunity();
        } else {
            Gate::authorize('create', [Customer::class, \App\Enums\CustomerStatus::LEAD]);
        }
    }
}
