<?php

namespace App\Livewire\Forms;

use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Masmerise\Toaster\Toaster;

class CustomerForm extends Form
{
    public ?Customer $customer = null;

    public $firstName = null;
    public $lastName = null;
    public $email = null;
    public $phone = null;
    public $dateOfBirth = null;
    public $address = null;
    public $postalCode = null;
    public $city = null;
    public $state = null;
    public $taxId = null;

    protected function rules()
    {
        return [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($this->customer?->id)],
            'phone' => 'required|string|min:10|max:24',
            'dateOfBirth' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'postalCode' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'taxId' => 'nullable|string|size:16',
        ];
    }

    protected function validationAttributes()
    {
        return [
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
        ];
    }

    /**
     * set customer for update
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        $this->firstName = $customer->first_name;
        $this->lastName = $customer->last_name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->dateOfBirth = $customer->date_of_birth;
        $this->address = $customer->address;
        $this->postalCode = $customer->postal_code;
        $this->city = $customer->city;
        $this->state = $customer->state;
        $this->taxId = $customer->tax_id;
    }

    /**
     * create customer
     */
    public function store()
    {
        $this->validate();

        try {
            $customer = DB::transaction(fn() => Customer::create($this->customerData()));

            Toaster::success('Cliente creato con successo');
            return $customer;
        } catch (Exception $e) {
            Log::error('Errore durante la creazione del cliente: ' . $e->getMessage());
            Toaster::error('Errore durante la creazione del cliente: ' . $e->getMessage());
        }
    }

    /**
     * update customer
     */
    public function update()
    {
        $this->validate();

        try {
            DB::transaction(fn() => $this->customer->update($this->customerData()));

            Toaster::success('Cliente aggiornato con successo');
            return $this->customer;
        } catch (Exception $e) {
            Log::error('Errore durante l\'aggiornamento del cliente: ' . $e->getMessage());
            Toaster::error('Errore durante l\'aggiornamento del cliente: ' . $e->getMessage());
        }
    }

    /**
     * customer data
     */
    private function customerData(): array
    {
        return [
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
        ];
    }
}
