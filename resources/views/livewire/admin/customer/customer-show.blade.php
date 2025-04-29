<div class="w-full">
    <x-button-back class="mb-2.5" route="customer.index" />
    <x-page-title label="Dettaglio Collaboratore" />

    <x-card class="w-3xl mx-auto">
        <x-card-header class="mb-6" label="Informazioni generali" />

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Nome: </span>
            <span>{{ $customer->full_name }}</span>
        </div>

        <div class="text-sm mb-2.5">
            <span class="text-gray-custom-4">Cellulare: </span>
            <span>{{ $customer->phone }}</span>
        </div>

        @if ($customer->email)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4" class="text-gray-custom-4">Email: </span>
                <span>{{ $customer->email }}</span>
            </div>
        @endif

        @if ($customer->date_of_birth)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Data di Nascita: </span>
                <span>{{ $customer->formatted_date_of_birth }}</span>
            </div>
        @endif

        @if ($customer->address)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Indirizzo: </span>
                <span>{{ $customer->address }}</span>
            </div>
        @endif

        @if ($customer->postal_code)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Cap: </span>
                <span>{{ $customer->postal_code }}</span>
            </div>
        @endif

        @if ($customer->city)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Città: </span>
                <span>{{ $customer->city }}</span>
            </div>
        @endif

        @if ($customer->state)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Provincia: </span>
                <span>{{ $customer->state }}</span>
            </div>
        @endif

        @if ($customer->tax_id)
            <div class="text-sm mb-2.5">
                <span class="text-gray-custom-4">Codice Fiscale: </span>
                <span>{{ $customer->tax_id }}</span>
            </div>
        @endif
    </x-card>
</div>
