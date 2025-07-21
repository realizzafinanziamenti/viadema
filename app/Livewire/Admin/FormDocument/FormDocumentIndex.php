<?php

namespace App\Livewire\Admin\FormDocument;

use App\Models\FormDocument;
use App\Traits\AcceptedFileTypes;
use App\Traits\HandlesEntityActions;
use Exception;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormDocumentIndex extends Component
{
    use WithPagination, WithoutUrlPagination, HandlesEntityActions, AcceptedFileTypes, WithFileUploads;

    public ?FormDocument $selectedDocument = null;
    public string $search = '';
    public string $filterDate = '';
    public ?string $title = null;
    public ?string $description = null;
    public ?TemporaryUploadedFile $file = null;

    protected function validationAttributes(): array
    {
        return [
            'title' => 'titolo',
            'description' => 'descrizione',
            'file' => 'file',
        ];
    }

    /**
     * open create document modal
     */
    public function openCreateDocumentModal(): void
    {
        $this->resetErrorBag();
        $this->reset(['title', 'description', 'file']);
        $this->dispatch('open-modal', 'document-create');
    }

    /**
     * This method is called when the user updates the file input.
     * It validates the file and updates the file property.
     */
    public function updatedFile()
    {
        // Dopo esser stati caricati, i file vengono validati per assicurarsi che siano del tipo corretto
        $validator = Validator::make(
            ['file' => $this->file],
            ['file' => ['required', 'file', 'mimetypes:' . implode(',', $this->acceptedFileTypesArray(['documents', 'excel'])), 'max:10240']],
            [
                'file.required' => 'Il file è obbligatorio.',
                'file.mimetypes' => 'Formato file non valido. I formati accettati sono: pdf, doc, docx, xls, xlsm, xlsx, csv.',
                'file.max' => 'Il file non può superare i 10MB.',
            ]
        );

        // Se la validazione fallisce, viene aggiunto un errore e il file viene resettato
        if ($validator->fails()) {
            $this->addError('file', $validator->errors()->first('file'));
            $this->reset('file');
            return;
        }

        // Se la validazione ha successo, rimuove eventuali errori precedenti e mantiene il file
        $this->clearValidation('file');
    }

    /**
     * This method is called when the user submits the form to create a new document.
     * It validates the input, creates a new FormDocument, and saves it to the database.
     */
    public function store(): void
    {
        Gate::authorize('create', FormDocument::class);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'mimetypes:' . implode(',', $this->acceptedFileTypesArray(['documents', 'excel'])), 'max:10240'],
        ]);

        try {
            $document = FormDocument::create([
                'title' => $this->title,
                'description' => $this->description,
                'user_id' => auth()->id(),
            ]);

            $attachment = $document->attachment()->create([
                'file_name' => $this->file->getClientOriginalName(),
                'file_path' => $this->file->store('documents', 'public'),
                'mime_type' => $this->file->getClientMimeType(),
                'file_size' => $this->file->getSize()
            ]);

            $document->attachment()->save($attachment);

            Toaster::success('Documento caricato con successo');
            $this->reset(['title', 'description', 'file']);
            $this->dispatch('close-modal', 'document-create');
        } catch (Exception $e) {
            Log::error('Errore durante il caricamento del documento: ' . $e->getMessage());
            Toaster::error('Errore durante il caricamento del documento: ' . $e->getMessage());
            $this->dispatch('close-modal', 'document-create');
        }
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected document and resets the selected document to null.
     */
    public function edit()
    {
        Gate::authorize('update', $this->selectedDocument);

        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->selectedDocument->update([
                'title' => $this->title,
                'description' => $this->description,
            ]);

            Toaster::success('Documento rinominato con successo');
            $this->reset(['title', 'description', 'file']);
            $this->dispatch('close-modal', 'document-edit');
        } catch (Exception $e) {
            Log::error('Errore durante la modifica del documento: ' . $e->getMessage());
            Toaster::error('Errore durante la modifica del documento: ' . $e->getMessage());
            $this->dispatch('close-modal', 'document-create');
        }
    }

    /**
     * This method is called when the user clicks the download button.
     * It retrieves the document by ID and returns a streamed response for download.
     */
    public function download($id): ?StreamedResponse
    {
        try {
            $document = FormDocument::findOrFail($id);
            Gate::authorize('download', $document);

            $attachment = $document->attachment;
            return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
        } catch (Exception $e) {
            Toaster::error('File non trovato o errore: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * This method is called when the user clicks the delete temporary file button.
     */
    public function deleteTemporaryFile()
    {
        $this->reset(['file']);
    }

    /**
     * This method is called when the user clicks the edit button.
     * It sets the selected document to be edited.
     */
    public function selectDocumentForUpdate(int $id)
    {
        $this->resetErrorBag();
        $this->selectEntityForAction(
            id: $id,
            modelClass: FormDocument::class,
            property: 'selectedDocument',
            modalName: 'document-edit',
            notFoundMessage: 'Documento non trovato'
        );
        if ($this->selectedDocument) {
            $this->title = $this->selectedDocument->title;
            $this->description = $this->selectedDocument->description;
        }
    }

    /**
     * This method is called when the user clicks the delete button.
     * It sets the selected document to be deleted.
     */
    public function selectDocumentForDelete(int $id)
    {
        $this->selectEntityForAction(
            id: $id,
            modelClass: FormDocument::class,
            property: 'selectedDocument',
            modalName: 'delete-document',
            notFoundMessage: 'Documento non trovato'
        );
    }

    /**
     * This method is called when the user clicks the delete button in the modal.
     * It deletes the selected document and resets the selected document to null.
     */
    public function deleteDocument()
    {
        Gate::authorize('delete', $this->selectedDocument);

        $this->deleteSelectedEntity(
            property: 'selectedDocument',
            modalName: 'delete-document',
            successMessage: 'Documento eliminato con successo',
        );
    }

    /**
     * Updated search bar callback function
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Updated filter date callback function
     */
    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function mount()
    {
        Gate::authorize('viewAny', FormDocument::class);
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $query = FormDocument::orderBy('created_at', 'desc');
        $query = $query->filterBySearch($this->search);
        $query = $query->filterByDate($this->filterDate);
        $formDocuments = $query->paginate(20);

        return view('livewire.admin.form-document.form-document-index', [
            'formDocuments' => $formDocuments,
        ]);
    }
}
