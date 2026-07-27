<?php

namespace App\Livewire\Admin\ActivityLog;

use App\Enums\CustomerStatus;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\PracticeStatus;
use App\Enums\ProductionType;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\ProductType;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class ActivityLogIndex extends Component
{
    use WithPagination, WithoutUrlPagination;

    public ?Activity $selectedLog = null;
    public int $paginate = 15;

    /**
     * Select a log to view its details and open the modal.
     */
    public function selectLogForDetails(int $logId)
    {
        $this->selectedLog = Activity::find($logId);
        Gate::authorize('view', $this->selectedLog);
        $this->dispatch('open-modal', 'log-details');
    }

    /**
     * Format field value for display
     */
    public function formatFieldValue($value): string
    {
        if (is_null($value)) {
            return '-';
        }

        return match ($value) {
            // Events
            'created' => 'Creazione',
            'updated' => 'Modifica',
            'deleted' => 'Eliminazione',
            'restored' => 'Ripristino',

            // Log names
            'user' => 'Collaboratori',
            'changed_department' => 'Cambio Dipartimento',
            'customer' => 'Clienti',
            'practice' => 'Pratiche',
            'lead' => 'Lead',
            'event' => 'Eventi',
            'event_participants' => 'Partecipanti Evento',
            'form_document' => 'Modulistica',
            'import_success' => 'Import completato',
            'import_failure' => 'Import fallito',
            'import_validation_failure' => 'Import fallito',

            default => ucfirst(str_replace('_', ' ', $value))
        };
    }

    /**
     * Convert value to string for display
     */
    public function convertValue($value, $field): string
    {
        if (is_null($value)) {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Sì' : 'No';
        }

        return match ($field) {
            'user_id' => $this->getUserName($value),
            'customer_id' => $this->getCustomerName($value),
            'product_type_id' => $this->getProductName($value),
            'date_of_birth' => $this->formatDate($value),
            'customer_type_id' => $this->getCustomerTypeName($value),
            'customer_status' => $this->getCustomerStatusLabel($value),
            'lead_status' => $this->getLeadStatusLabel($value),
            'lead_source' => $this->getLeadSourceLabel($value),
            'acquisition_channel' => $this->getLeadSourceLabel($value),
            'inserted_at' => $this->formatDate($value),
            'first_installment_date' => $this->formatDate($value),
            'last_installment_date' => $this->formatDate($value),
            'early_settlement_date' => $this->formatDate($value),
            'disbursement_date' => $this->formatDate($value),
            'renewability_date' => $this->formatDate($value),
            'alert_date' => $this->formatDate($value),
            'practice_status' => $this->getPracticeStatusLabel($value),
            'production_type' => $this->getProductionTypeLabel($value),
            default => (string) $value,
        };
    }

    /**
     * Get user name by ID
     */
    private function getUserName($userId): string
    {
        if (!$userId) return 'Non assegnato';

        static $users = [];

        if (!isset($users[$userId])) {
            $user = User::find($userId);
            $users[$userId] = $user ? $user->full_name : "Utente #{$userId}";
        }

        return $users[$userId];
    }

    /**
     * Get customer name by ID
     */
    private function getCustomerName($customerId): string
    {
        if (!$customerId) return 'Non assegnato';

        static $customers = [];

        if (!isset($customers[$customerId])) {
            $customer = Customer::find($customerId);
            $customers[$customerId] = $customer ? $customer->full_name : "Cliente #{$customerId}";
        }

        return $customers[$customerId];
    }

    /**
     * Get product name by ID
     */
    private function getProductName($productId): string
    {
        if (!$productId) return 'Non assegnato';

        static $products = [];

        if (!isset($products[$productId])) {
            $product = ProductType::find($productId);
            $products[$productId] = $product ? $product->name : "Prodotto #{$productId}";
        }

        return $products[$productId];
    }

    /**
     * Get customer type name by ID
     */
    private function getCustomerTypeName($customerTypeId): string
    {
        if (!$customerTypeId) return 'Non specificato';

        static $customerTypes = [];

        if (!isset($customerTypes[$customerTypeId])) {
            $customerType = CustomerType::find($customerTypeId);
            $customerTypes[$customerTypeId] = $customerType ? $customerType->name : "Tipo #{$customerTypeId}";
        }

        return $customerTypes[$customerTypeId];
    }

    /**
     * Get customer status label
     */
    private function getCustomerStatusLabel($customerStatus): string
    {
        return match ($customerStatus) {
            CustomerStatus::LEAD->value => CustomerStatus::LEAD->getLabelText(),
            CustomerStatus::CUSTOMER->value => CustomerStatus::CUSTOMER->getLabelText(),
            default => ucfirst(str_replace('_', ' ', $customerStatus))
        };
    }

    /**
     * Get lead status label
     */
    private function getLeadStatusLabel($leadStatus): string
    {
        if (!$leadStatus) return 'Non specificato';

        return match ($leadStatus) {
            LeadStatus::NEW->value => LeadStatus::NEW->getLabelText(),
            LeadStatus::TO_RECONTACT->value => LeadStatus::TO_RECONTACT->getLabelText(),
            LeadStatus::IN_NEGOTIATION->value => LeadStatus::IN_NEGOTIATION->getLabelText(),
            LeadStatus::NOT_FEASIBLE->value => LeadStatus::NOT_FEASIBLE->getLabelText(),
            LeadStatus::FEASIBLE->value => LeadStatus::FEASIBLE->getLabelText(),
            default => ucfirst(str_replace('_', ' ', $leadStatus))
        };
    }

    /**
     * Get lead source label
     */
    private function getLeadSourceLabel($leadSource): string
    {
        if (!$leadSource) {
            return 'Non specificato';
        }

        return LeadSource::tryFrom((string) $leadSource)?->getLabelText()
            ?? ucfirst(str_replace('_', ' ', (string) $leadSource));
    }

    /**
     * Get practice status label
     */
    private function getPracticeStatusLabel($practiceStatus): string
    {
        if (!$practiceStatus) return 'Non specificato';

        return match ($practiceStatus) {
            PracticeStatus::UNDER_REVIEW->value => PracticeStatus::UNDER_REVIEW->getLabelText(),
            PracticeStatus::REJECTED->value => PracticeStatus::REJECTED->getLabelText(),
            PracticeStatus::APPROVED->value => PracticeStatus::APPROVED->getLabelText(),
            PracticeStatus::SUSPENDED->value => PracticeStatus::SUSPENDED->getLabelText(),
            PracticeStatus::PENDING->value => PracticeStatus::PENDING->getLabelText(),
            PracticeStatus::DISBURSED->value => PracticeStatus::DISBURSED->getLabelText(),
            default => ucfirst(str_replace('_', ' ', $practiceStatus))
        };
    }

    /**
     * Get production type label
     */
    private function getProductionTypeLabel($productionType): string
    {
        if (!$productionType) return 'Non specificato';

        return match ($productionType) {
            ProductionType::DIRECT->value => ProductionType::DIRECT->getLabelText(),
            ProductionType::INDIRECT->value => ProductionType::INDIRECT->getLabelText(),
            default => ucfirst(str_replace('_', ' ', $productionType))
        };
    }

    /**
     * Format date
     */
    private function formatDate($date): string
    {
        if (!$date) return 'Non specificato';

        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (Exception $e) {
            return (string) $date;
        }
    }

    /**
     * Get the activity logs with pagination.
     */
    protected function getActivityLogs()
    {
        return Activity::with('causer')
            ->latest()
            ->paginate($this->paginate);
    }

    public function mount()
    {
        Gate::authorize('viewAny', Activity::class);
    }

    public function render()
    {
        return view('livewire.admin.activity-log.activity-log-index', [
            'activityLogs' => $this->getActivityLogs(),
        ]);
    }
}