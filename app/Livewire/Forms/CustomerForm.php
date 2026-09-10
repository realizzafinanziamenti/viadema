<?php

namespace App\Livewire\Forms;

use App\Enums\CustomerStatus;
use App\Enums\LeadStatus;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\UserAddedToCustomer;
use Closure;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
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

    protected function rules(): array
    {
        return array_merge(
            [
                'customerTypeId' => [
                    'nullable',
                    'exists:customer_types,id',
                ],

                'firstName' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'lastName' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique(
                        'customers',
                        'email'
                    )->ignore(
                        $this->customer?->id
                    ),
                ],

                /*
                 * LEAD identity:
                 *
                 * first_name + last_name + phone
                 *
                 * The rule is attached directly to phone so
                 * Livewire can correctly render the error on
                 * form.phone.
                 */
                'phone' => [
                    'required',
                    'string',
                    'min:10',
                    'max:24',

                    function (
                        string $attribute,
                        mixed $value,
                        Closure $fail
                    ): void {
                        if (
                            $this->customerStatus
                            !== CustomerStatus::LEAD->value
                        ) {
                            return;
                        }

                        /*
                         * Required validation will handle
                         * incomplete names separately.
                         */
                        if (
                            blank($this->firstName)
                            || blank($this->lastName)
                        ) {
                            return;
                        }

                        $firstName = mb_strtolower(
                            trim(
                                (string) $this->firstName
                            )
                        );

                        $lastName = mb_strtolower(
                            trim(
                                (string) $this->lastName
                            )
                        );

                        $query = Customer::query()
                            ->whereRaw(
                                'LOWER(TRIM(first_name)) = ?',
                                [$firstName]
                            )
                            ->whereRaw(
                                'LOWER(TRIM(last_name)) = ?',
                                [$lastName]
                            )
                            ->where(
                                'phone',
                                $value
                            );

                        /*
                         * During Lead update, the current
                         * record must not duplicate itself.
                         */
                        if (
                            $this->customer !== null
                        ) {
                            $query->where(
                                'id',
                                '!=',
                                $this->customer->getKey()
                            );
                        }

                        if ($query->exists()) {
                            $fail(
                                'Esiste già un profilo con lo stesso nome, cognome e numero di telefono.'
                            );
                        }
                    },
                ],

                'dateOfBirth' => [
                    'nullable',
                    'date',
                ],

                'address' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'postalCode' => [
                    'nullable',
                    'string',
                    'max:10',
                ],

                'city' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'state' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                /*
                 * LEAD:
                 * taxId is optional.
                 *
                 * CUSTOMER:
                 * taxId is mandatory and unique.
                 */
                'taxId' => [
                    $this->customerStatus
                        === CustomerStatus::CUSTOMER->value
                            ? 'required'
                            : 'nullable',

                    'string',
                    'size:16',

                    Rule::unique(
                        'customers',
                        'tax_id'
                    )->ignore(
                        $this->customer?->id
                    ),
                ],

                'customerStatus' => [
                    'required',
                    'string',
                    new Enum(CustomerStatus::class),
                ],

                'notes' => [
                    'nullable',
                    'string',
                    'max:65535',
                ],

                'recontactDate' => [
                    'nullable',
                    'date',
                ],
            ],

            $this->userIdRules(),

            $this->leadStatusRules()
        );
    }

    /**
     * Lead status is required only for Leads.
     */
    protected function leadStatusRules(): array
    {
        $rules = [
            $this->customerStatus
                === CustomerStatus::LEAD->value
                    ? 'required'
                    : 'nullable',

            'string',

            new Enum(LeadStatus::class),
        ];

        return [
            'leadStatus' => $rules,
        ];
    }

    /**
     * User assignment rules.
     */
    protected function userIdRules(): array
    {
        if (
            auth()->user()->can(
                'assign customer to user'
            )
        ) {
            return [
                'userId' => [
                    'required',
                    'exists:users,id',
                ],
            ];
        }

        return [
            'userId' => [
                'nullable',
            ],
        ];
    }

    protected function validationAttributes(): array
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
            'recontactDate' => 'data ricontatto',
        ];
    }

    /**
     * Populate the form from an existing Customer/Lead.
     */
    public function setCustomer(
        ?Customer $customer
    ): void {
        if ($customer === null) {
            $this->resetCustomerForm();

            return;
        }

        $this->customer = $customer;

        $this->userId =
            $customer->user_id;

        $this->customerTypeId =
            $customer->customer_type_id;

        $this->firstName =
            $customer->first_name;

        $this->lastName =
            $customer->last_name;

        $this->email =
            $customer->email;

        $this->phone =
            $customer->phone;

        $this->dateOfBirth =
            $customer->date_of_birth
                ?->format('Y-m-d');

        $this->address =
            $customer->address;

        $this->postalCode =
            $customer->postal_code;

        $this->city =
            $customer->city;

        $this->state =
            $customer->state;

        $this->taxId =
            $customer->tax_id;

        $this->customerStatus =
            $customer->customer_status?->value;

        $this->leadStatus =
            $customer->lead_status?->value;

        $this->recontactDate =
            $customer->recontact_date
                ?->format('Y-m-d');

        $this->notes =
            $customer->notes;
    }

    /**
     * Reset customer form.
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
     * Normalize fields used as identity keys.
     */
    private function normalizeIdentityFields(): void
    {
        $this->firstName = trim(
            (string) $this->firstName
        );

        $this->lastName = trim(
            (string) $this->lastName
        );

        if (filled($this->phone)) {
            $normalizedPhone = preg_replace(
                '/[\s\-\(\)]/',
                '',
                trim(
                    (string) $this->phone
                )
            );

            $this->phone = is_string(
                $normalizedPhone
            )
                ? $normalizedPhone
                : null;
        }

        $this->email = filled($this->email)
            ? trim(
                (string) $this->email
            )
            : null;

        $this->taxId = filled($this->taxId)
            ? mb_strtoupper(
                preg_replace(
                    '/\s+/',
                    '',
                    trim(
                        (string) $this->taxId
                    )
                )
            )
            : null;
    }

    /**
     * Normalize, validate and return data ready
     * for persistence.
     *
     * Used both by CustomerForm itself and by
     * Practice creation.
     */
    public function validatedCustomerData(): array
    {
        $this->normalizeIdentityFields();

        $this->validate();

        return $this->customerData();
    }

    /**
     * Create a Customer or Lead.
     */
    public function store(): ?Customer
    {
        /*
         * Validation stays outside the catch intentionally.
         * Livewire must receive validation errors directly
         * and render them on the form fields.
         */
        $data =
            $this->validatedCustomerData();

        try {
            $customer = DB::transaction(
                fn (): Customer =>
                    Customer::create($data)
            );

            $user = User::find(
                $customer->user_id
            );

            if ($user !== null) {
                Notification::send(
                    $user,
                    new UserAddedToCustomer(
                        $customer
                    )
                );
            }

            Toaster::success(
                'Profilo creato con successo'
            );

            return $customer;
        } catch (Exception $exception) {
            Log::error(
                'Errore durante la creazione del profilo: '
                . $exception->getMessage(),
                [
                    'exception' => $exception,
                ]
            );

            Toaster::error(
                'Errore durante la creazione del profilo: '
                . $exception->getMessage()
            );

            return null;
        }
    }

    /**
     * Update an existing Customer or Lead.
     */
    public function update(): ?Customer
    {
        $data =
            $this->validatedCustomerData();

        try {
            $oldUserId =
                $this->customer->user_id;

            DB::transaction(
                fn (): bool =>
                    $this->customer->update(
                        $data
                    )
            );

            if (
                $oldUserId
                !== $this->customer->user_id
            ) {
                $user = User::find(
                    $this->customer->user_id
                );

                if ($user !== null) {
                    Notification::send(
                        $user,
                        new UserAddedToCustomer(
                            $this->customer
                        )
                    );
                }
            }

            Toaster::success(
                'Profilo aggiornato con successo'
            );

            return $this->customer;
        } catch (Exception $exception) {
            Log::error(
                'Errore durante l\'aggiornamento del profilo: '
                . $exception->getMessage(),
                [
                    'customer_id' =>
                        $this->customer?->getKey(),
                    'exception' => $exception,
                ]
            );

            Toaster::error(
                'Errore durante l\'aggiornamento del profilo: '
                . $exception->getMessage()
            );

            return null;
        }
    }

    /**
     * Build persistence payload.
     */
    private function customerData(): array
    {
        $isLead =
            $this->customerStatus
            === CustomerStatus::LEAD->value;

        return [
            'user_id' =>
                auth()->user()->can(
                    'assign customer to user'
                )
                    ? $this->userId
                    : auth()->id(),

            'customer_type_id' =>
                $this->customerTypeId
                    ?: null,

            'first_name' =>
                $this->firstName,

            'last_name' =>
                $this->lastName,

            'email' =>
                $this->email
                    ?: null,

            'phone' =>
                $this->phone,

            'date_of_birth' =>
                $this->dateOfBirth
                    ?: null,

            'address' =>
                $this->address
                    ?: null,

            'postal_code' =>
                $this->postalCode
                    ?: null,

            'city' =>
                $this->city
                    ?: null,

            'state' =>
                $this->state
                    ?: null,

            /*
             * Leads deliberately do not persist a tax ID.
             *
             * When the Lead enters the Practice workflow,
             * CustomerForm is switched to CUSTOMER and
             * taxId becomes required.
             */
            'tax_id' =>
                $isLead
                    ? null
                    : ($this->taxId ?: null),

            'customer_status' =>
                $this->customerStatus,

            'lead_status' =>
                $isLead
                    ? ($this->leadStatus ?: null)
                    : null,

            'recontact_date' =>
                $isLead
                    ? ($this->recontactDate ?: null)
                    : null,

            'notes' =>
                $this->notes
                    ?: null,
        ];
    }
}
