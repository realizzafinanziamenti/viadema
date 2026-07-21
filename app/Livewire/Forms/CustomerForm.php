<?php

namespace App\Livewire\Forms;

use App\Enums\CustomerStatus;
use App\Enums\LeadCommunication;
use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\UserAddedToCustomer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Masmerise\Toaster\Toaster;

class CustomerForm extends Form
{
    public ?Customer $customer = null;

    public ?int $userId = null;
    public ?int $customerTypeId = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $email = null;
    public ?string $phone = null;
    public ?string $dateOfBirth = null;
    public ?string $address = null;
    public ?string $postalCode = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $taxId = null;
    public ?string $customerStatus = null;
    public ?string $leadStatus = null;
    public ?string $notes = null;
    public ?string $recontactDate = null;

    protected function rules()
    {
        return array_merge(
            [
                'customerTypeId' => ['nullable', 'exists:customer_types,id'],
                'firstName' => ['required', 'string', 'max:255'],
                'lastName' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($this->customer?->id)],
                'phone' => ['required', 'string', 'min:10', 'max:24'],
                'dateOfBirth' => ['nullable', 'date'],
                'address' => ['nullable', 'string', 'max:255'],
                'postalCode' => ['nullable', 'string', 'max:10'],
                'city' => ['nullable', 'string', 'max:255'],
                'state' => ['nullable', 'string', 'max:255'],
                'taxId' => ['nullable', 'string', 'size:16', Rule::unique('customers', 'tax_id')->ignore($this->customer?->id)],
                'customerStatus' => ['required', 'string', new Enum(CustomerStatus::class)],
                'notes' => ['nullable', 'string', 'max:65535'],
                'recontactDate' => ['nullable', 'date'],
            ],
            $this->userIdRules(),
            $this->leadStatusRules()
        );
    }

    /**
     * customer status rules
     * if customer is a lead, lead status is required
     */
    protected function leadStatusRules(): array
    {
        $leadStatusRules = [$this->customerStatus === CustomerStatus::LEAD->value ? 'required' : 'nullable'];
        $leadStatusRules[] = 'string';
        $leadStatusRules[] = new Enum(LeadStatus::class);

        return [
            'leadStatus' => $leadStatusRules,
        ];
    }

    /**
     * userId rules
     * if user is not allowed to assign customer to user, assign customer to current user
     */
    protected function userIdRules(): array
    {
        if (auth()->user()->can('assign customer to user')) {
            return [
                'userId' => ['required', 'exists:users,id'],
            ];
        }

        return [
            'userId' => ['nullable'],
        ];
    }

    protected function validationAttributes()
    {
        return [
            'userId' => 'collaboratore',
            'customerTypeId' => 'tipologia cliente',
            'firstName' => 'nome',
            'lastName' => 'cognome',
            'email' => 'email',
            'phone' => 'cellulare',
            'dateOfBirth' => 'data di nascita',
            'address' => 'indirizzo',
            'postalCode' => 'CAP',
            'city' => 'città',
            'state' => 'provincia',
            'taxId' => 'codice fiscale',
            'customerStatus' => 'stato cliente',
            'leadStatus' => 'stato lead',
            'notes' => 'note',
        ];
    }

    /**
     * set customer for update
     */
    public function setCustomer(?Customer $customer)
    {
        if (is_null($customer)) {
            $this->resetCustomerForm();
            return;
        }

        $this->customer = $customer;

        $this->userId = $customer->user_id;
        $this->customerTypeId = $customer->customer_type_id;
        $this->firstName = $customer->first_name;
        $this->lastName = $customer->last_name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->dateOfBirth = $customer->date_of_birth?->format('Y-m-d');
        $this->address = $customer->address;
        $this->postalCode = $customer->postal_code;
        $this->city = $customer->city;
        $this->state = $customer->state;
        $this->taxId = $customer->tax_id;
        $this->customerStatus = $customer->customer_status?->value;
        $this->leadStatus = $customer->lead_status?->value;
        $this->recontactDate = $customer->recontact_date?->format('Y-m-d');
        $this->notes = $customer->notes;
    }

    /**
     * Reset all form fields to null.
     */
    protected function resetCustomerForm(): void
    {
        $this->customer = null;
        $this->userId = null;
        $this->customerTypeId = null;
        $this->firstName = null;
        $this->lastName = null;
        $this->email = null;
        $this->phone = null;
        $this->dateOfBirth = null;
        $this->address = null;
        $this->postalCode = null;
        $this->city = null;
        $this->state = null;
        $this->taxId = null;
        $this->customerStatus = null;
        $this->leadStatus = null;
        $this->recontactDate = null;
        $this->notes = null;
    }

    /**
     * create customer
     */
    public function store()
    {
        $this->validate();

        try {
            $customer = DB::transaction(fn() => Customer::create($this->customerData()));

            // notify participants
            Notification::send(
                User::find($customer->user_id),
                new UserAddedToCustomer($customer)
            );

            Toaster::success('Profilo creato con successo');
            return $customer;
        } catch (Exception $e) {
            Log::error('Errore durante la creazione del profilo: ' . $e->getMessage());
            Toaster::error('Errore durante la creazione del profilo: ' . $e->getMessage());
        }
    }

    /**
     * update customer
     */
    public function update()
    {
        $this->validate();

        try {
            // get old user id for notification
            $oldUserId = $this->customer->user_id;

            DB::transaction(fn() => $this->customer->update($this->customerData()));

            // if user changed, notify new user
            if ($oldUserId !== $this->customer->user_id) {
                Notification::send(
                    User::find($this->customer->user_id),
                    new UserAddedToCustomer($this->customer)
                );
            }

            Toaster::success('Profilo aggiornato con successo');
            return $this->customer;
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento del profilo: ' . $e->getMessage());
            Toaster::error('Errore durante l\'aggiornamento del profilo: ' . $e->getMessage());
        }
    }

    /**
     * customer data
     */
    private function customerData(): array
    {
        return [
            // if user is not allowed to assign customer to user, assign customer to current user
            'user_id' => auth()->user()->can('assign customer to user') ? $this->userId : auth()->id(),
            'customer_type_id' => $this->customerTypeId ?: null,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email ?: null,
            'phone' => $this->phone,
            'date_of_birth' => $this->dateOfBirth ?: null,
            'address' => $this->address ?: null,
            'postal_code' => $this->postalCode ?: null,
            'city' => $this->city ?: null,
            'state' => $this->state ?: null,
            'tax_id' => $this->taxId ?: null,
            'customer_status' => $this->customerStatus,
            'lead_status' => $this->leadStatus ?: null,
            'recontact_date' => $this->recontactDate ?: null,
            'notes' => $this->notes ?: null,
        ];
    }
}