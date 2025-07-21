<?php

namespace App\Livewire\Admin\Practice;

use App\Livewire\Forms\CustomerForm;
use App\Livewire\Forms\PracticeForm;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\FinancialTable;
use App\Models\Installment;
use App\Models\InstallmentProductDefault;
use App\Models\Insurance;
use App\Models\Practice;
use App\Models\ProductSubtype;
use App\Models\ProductType;
use App\Models\User;
use App\Traits\AcceptedFileTypes;
use App\Traits\HandlesEntityActions;
use App\Traits\HandlesPracticeInstallments;
use App\Traits\InteractsWithDropdowns;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PracticeUpdate extends Component
{
    use InteractsWithDropdowns, HandlesPracticeInstallments, AcceptedFileTypes, WithFileUploads, HandlesEntityActions;

    public Practice $practice;
    public PracticeForm $practiceForm;
    public CustomerForm $customerForm;
    public ?Customer $selectedCustomer = null;
    public ?Attachment $selectedAttachment = null;
    public array $temporaryFiles = [];
    public array $productTypes = [];
    public array $productSubtypes = [];
    public array $financialTables = [];
    public array $insurances = [];
    public array $installments = [];
    public array $customerTypes = [];
    public array $practiceStatuses = [];
    public int $step = 1;
    public string $teamMemberSearch = '';
    public string $customerSearch = '';
    public bool $selectsInitialized = false;

    /**
     * Set team member for customer form
     */
    public function setTeamMember(?int $value = null): void
    {
        $this->setFormSelectValue('userId', $value, 'customerForm');
    }

    /**
     * Set team member for practice form
     */
    public function setPracticeTeamMember(?int $value = null): void
    {
        $this->setFormSelectValue('userId', $value, 'practiceForm');
    }

    /**
     * Set customer
     */
    public function setCustomer(?int $value = null): void
    {
        $this->setFormSelectValue('customerId', $value, 'practiceForm');

        $this->resetValidation('practiceForm.customerId');
        $this->selectedCustomer = Customer::find($this->practiceForm->customerId);
    }

    /**
     * Set product type
     */
    public function setProductType(?int $value = null): void
    {
        $this->setFormSelectValue('productTypeId', $value, 'practiceForm');
        $this->setRenewabilityAndAlertPercentage($this->practiceForm);
        $this->recalculateRenewabilityDate($this->practiceForm);
    }

    /**
     * Set product subtype
     */
    public function setProductSubtype(?int $value = null): void
    {
        $this->setFormSelectValue('productSubtypeId', $value, 'practiceForm');
    }

    /**
     * Set financial table
     */
    public function setFinancialTable(?int $value = null): void
    {
        $this->setFormSelectValue('financialTableId', $value, 'practiceForm');
    }

    /**
     * Set insurance
     */
    public function setInsurance(?int $value = null): void
    {
        $this->setFormSelectValue('insuranceId', $value, 'practiceForm');
    }

    /**
     * Set installment
     */
    public function setInstallment(?int $value = null): void
    {
        $this->setFormSelectValue('installmentId', $value, 'practiceForm');
        $this->recalculateLastInstallmentDate($this->practiceForm, $this->installments);
        $this->setRenewabilityAndAlertPercentage($this->practiceForm);
        $this->recalculateRenewabilityDate($this->practiceForm);
    }

    /**
     * Update first installment date callback function and recalculate last installment and renewability date
     */
    public function updatedPracticeFormFirstInstallmentDate(): void
    {
        if ($this->practiceForm->firstInstallmentDate) {
            $this->recalculateLastInstallmentDate($this->practiceForm, $this->installments);
            $this->recalculateRenewabilityDate($this->practiceForm);
        } else {
            $this->practiceForm->lastInstallmentDate = null;
            $this->practiceForm->renewabilityDate = null;
        }
    }

    /**
     * Update practice form renewability percentage and recalculate renewability date
     */
    public function updatedPracticeFormRenewabilityPercentage(): void
    {
        $this->recalculateRenewabilityDate($this->practiceForm);
    }

    /**
     * Set customer type
     */
    public function setCustomerType(?int $value = null): void
    {
        $this->setFormSelectValue('customerTypeId', $value, 'practiceForm');
    }

    /**
     * open create customer modal
     */
    public function openCreateCustomerModal(): void
    {
        $this->teamMemberSearch = '';
        $this->dispatch('open-modal', 'customer-create');
    }

    /**
     * first next step function
     */
    public function firstNextStep(): void
    {
        if (! $this->practiceForm->customerId) {
            $this->addError('practiceForm.customerId', 'Seleziona prima un cliente.');
            return;
        }

        $this->teamMemberSearch = '';
        $this->step = 2;
        $this->dispatch('step-changed');
    }

    /**
     * first previous step function
     */
    public function firstPrevStep(): void
    {
        $this->step = 1;
        $this->dispatch('step-changed');
    }

    /**
     * Initialize the selects
     */
    protected function initializeSelects(): void
    {
        $this->productTypes = ProductType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->productSubtypes = ProductSubtype::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->financialTables = FinancialTable::orderBy('percentage')
            ->pluck('percentage', 'id')
            ->toArray();

        $this->insurances = Insurance::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $this->installments = Installment::orderBy('value')
            ->pluck('value', 'id')
            ->toArray();

        $this->customerTypes = CustomerType::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * second next step function
     */
    public function secondNextStep(): void
    {
        $this->practiceForm->validate();
        $this->step = 3;
        $this->dispatch('step-changed');
    }

    /**
     * second previous step function
     */
    public function secondPrevStep(): void
    {
        $this->step = 2;
        $this->dispatch('step-changed');
    }

    /**
     * Save customer
     */
    public function saveCustomer(): void
    {
        Gate::authorize('create', Customer::class);
        $customer = $this->customerForm->store();

        $this->selectedCustomer = $customer;
        $this->practiceForm->customerId = $customer->id;
        $this->dispatch('close-modal', 'customer-create');
    }

    /**
     * Save practice
     */
    public function savePractice(): void
    {
        Gate::authorize('update', $this->practice);
        $practice = $this->practiceForm->update();

        $this->redirectRoute('practice.show', ['id' => $practice->id], navigate: true);
    }

    /**
     * This method is called when the user uploads new files.
     * It updates the practice form attachments with the temporary files.
     */
    public function updatedTemporaryFiles(): void
    {
        $this->validate([
            'temporaryFiles' => ['nullable', 'array', 'max:10'],
            'temporaryFiles.*' => ['nullable', 'file', 'mimetypes:' . implode(',', $this->acceptedFileTypesArray()), 'max:10240']
        ], [
            'temporaryFiles.max' => 'Puoi caricare al massimo 10 file.',
            'temporaryFiles.*.max' => 'Ogni file non può superare i 10MB.',
            'temporaryFiles.*.mimetypes' => 'Formato file non valido.',
        ]);

        foreach ($this->temporaryFiles as $file) {
            $this->practiceForm->attachments[] = $file;
        }
    }

    /**
     * This method is called when the user deletes a temporary file.
     * It removes the file from the temporary files array and practice form attachments.
     *
     * @param int|string $index The index of the file to delete
     */
    public function deleteTemporaryFile(int $index): void
    {
        // Remove from practice form attachments
        if (isset($this->practiceForm->attachments[$index])) {
            unset($this->practiceForm->attachments[$index]);
            $this->practiceForm->attachments = array_values($this->practiceForm->attachments);
        }
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
        $this->practice = Practice::find($id);
        $this->selectedCustomer = $this->practice->customer;
        Gate::authorize('update', $this->practice);

        $this->practiceForm->setPractice($this->practice);
        $this->initializeSelects();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $teamMembers = User::assignableUsers()
            ->filterBySearch($this->teamMemberSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        $customers = Customer::filterBySearch($this->customerSearch)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.practice.practice-update', [
            'teamMembers' => $teamMembers,
            'customers' => $customers,
            'selectedUserId' => $this->customerForm->userId,
        ]);
    }
}
