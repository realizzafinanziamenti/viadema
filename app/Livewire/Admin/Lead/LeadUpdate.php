<?php

namespace App\Livewire\Admin\Lead;

use App\Enums\LeadCommunication;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Livewire\Forms\CustomerForm;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\User;
use App\Traits\EnumHelper;
use App\Traits\InteractsWithDropdowns;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Livewire\Forms\PracticeOpportunityForm;
use Illuminate\Support\Facades\DB;
use App\Enums\ProductionType;
use App\Models\FinancialTable;
use App\Models\ProductType;
use App\Models\ProductSubtype;
use App\Models\Installment;
use App\Models\Insurance;
use App\Services\PracticeAttachments;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class LeadUpdate extends Component
{
    use InteractsWithDropdowns, EnumHelper;
    use \App\Traits\HandlesLeadAttachments;

    public PracticeOpportunityForm $opportunityForm;

    public Customer $lead;
    // customer form component
    public CustomerForm $form;
    public string $search = '';
    public array $teamMembers = [];
    public array $customerTypes = [];
    public array $leadSources = [];
    public array $leadStatuses = [];
    public array $productTypes = [];
    public array $productSubtypes = [];
    public array $installments = [];
    public array $insurances = [];
    public array $financialTables = [];
    public array $productionTypes = [];
    public function setOpportunityIsRenewal($value = null): void
    {
        $this->opportunityForm->isRenewal = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    public function setOpportunityProductionType(?string $value = null): void
    {
        $this->opportunityForm->productionType = $value;
    }

    public function setOpportunityCustomerType(?int $value = null): void
    {
        $this->opportunityForm->customerTypeId = $value;
    }

    public function setOpportunityFinancialTable(?int $value = null): void
    {
        $this->opportunityForm->financialTableId = $value;
    }

    /**
     * Set title customer
     */
    public function setTeamMember(?int $value = null): void
    {
        $this->setFormSelectValue('userId', $value);
    }

    /**
     * Set customer type
     */
    public function setCustomerType(?int $value = null): void
    {
        $this->setFormSelectValue('customerTypeId', $value);
    }

/**
 * Set acquisition channel on the practice opportunity.
 */
public function setOpportunityAcquisitionChannel(?string $value = null): void
{
    $this->setFormSelectValue(
        'acquisitionChannel',
        $value,
        form: 'opportunityForm'
    );
}

    /**
     * Set lead status
     */
    public function setLeadStatus(?string $value = null): void
    {
        $this->setFormSelectValue('leadStatus', $value);
    }

    /**
     * edit lead
     */
    public function save(): void
    {
        Gate::authorize('update', $this->lead->fresh());
        $this->validateAttachments('attachments');
        $stored = [];

        try {
            $lead = DB::transaction(function () use (&$stored) {
                $lead = Customer::lockForUpdate()->findOrFail($this->lead->id);
                Gate::authorize('update', $lead);
                abort_unless($lead->isLead(), 404);
                // Reject a stale editor rather than attach to a newly selected opportunity.
                abort_unless($lead->latestPracticeOpportunity()->value('id') === $this->attachmentOpportunityId, 409);
                $this->opportunityForm->opportunity = $this->attachmentOpportunity();
                $this->form->customer = $lead;
                $lead = $this->form->update();
                if (! $lead) {
                    throw new RuntimeException('Salvataggio del lead non riuscito.');
                }
                $opportunity = $this->opportunityForm->updateOrCreate($lead);
                foreach ($this->attachments as $file) {
                    $stored[] = app(PracticeAttachments::class)->store($opportunity, $file)->file_path;
                }
                return $lead;
            });
        } catch (Throwable $exception) {
            foreach ($stored as $path) {
                Storage::disk('public')->delete($path);
            }
            if ($exception instanceof ValidationException
                || $exception instanceof AuthorizationException
                || $exception instanceof HttpException) {
                throw $exception;
            }
            report($exception);
            Toaster::error('Errore durante il salvataggio del lead e degli allegati. Riprova.');
            return;
        }
        $this->attachments = [];
        $this->temporaryFiles = [];
        $this->attachmentOpportunityId = $lead->latestPracticeOpportunity()->value('id');

        $this->redirectRoute('lead.show', ['id' => $lead->id], navigate: true);
    }
    public function setOpportunityProductType(?int $value = null): void
    {
        $this->opportunityForm->productTypeId = $value;
    }

    public function setOpportunityProductSubtype(?int $value = null): void
    {
        $this->opportunityForm->productSubtypeId = $value;
    }

    public function setOpportunityInstallment(?int $value = null): void
    {
        $this->opportunityForm->installmentId = $value;
    }

    public function setOpportunityInsurance(?int $value = null): void
    {
        $this->opportunityForm->insuranceId = $value;
    }
    /**
     * Initialize lists.
     */
    public function initializeLists(): void
    {
        $this->productTypes = ProductType::orderBy('name')->pluck('name', 'id')->toArray();
        $this->productSubtypes = ProductSubtype::orderBy('name')->pluck('name', 'id')->toArray();
        $this->installments = Installment::orderBy('value')->pluck('value', 'id')->toArray();
        $this->insurances = Insurance::orderBy('name')->pluck('name', 'id')->toArray();
        $this->financialTables = FinancialTable::orderBy('percentage')->pluck('percentage', 'id')->toArray();
        $this->productionTypes = $this->getEnumOptions(ProductionType::class);
        $this->customerTypes = CustomerType::orderBy('name')
        ->pluck('name', 'id')
        ->toArray();

        $this->leadSources = $this->getEnumOptions(LeadSource::class);
        $this->leadStatuses = $this->getEnumOptions(LeadStatus::class);
    }

    public function mount($id)
    {
        $this->lead = Customer::leads()->findOrFail($id);
        Gate::authorize('update', $this->lead);

        $this->form->setCustomer($this->lead);
        $this->opportunityForm->setOpportunity(
            $this->lead->latestPracticeOpportunity
        );
        $this->attachmentOpportunityId = $this->opportunityForm->opportunity?->id;
        $this->initializeLists();
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        Gate::authorize('view', $this->lead->fresh());
        $this->teamMembers = User::assignableUsers()
            ->filterBySearch($this->search)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->pluck('full_name', 'id')
            ->toArray();

        return view('livewire.admin.lead.lead-update', [
            'existingAttachments' => $this->attachmentOpportunity()?->attachments()->get() ?? collect(),
            'teamMembers' => $this->teamMembers,
            'selectedUserId' => $this->form->userId,

            'productTypes' => $this->productTypes,
            'productSubtypes' => $this->productSubtypes,
            'installments' => $this->installments,
            'insurances' => $this->insurances,
            'financialTables' => $this->financialTables,
            'productionTypes' => $this->productionTypes,
        ]);
    }
}
