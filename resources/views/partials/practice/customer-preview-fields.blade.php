<div class="grid grid-cols-2 gap-6 col-span-2">
    {{-- First Name --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Nome</flux:label>
        <x-display-input value="{{ $selectedCustomer?->first_name }}" />
    </div>
    {{-- Last Name --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Cognome</flux:label>
        <x-display-input value="{{ $selectedCustomer?->last_name }}" />
    </div>
    {{-- Phone --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Cellulare</flux:label>
        <x-display-input value="{{ $selectedCustomer?->phone }}" />
    </div>
    {{-- Date of Birth --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data di Nascita</flux:label>
        <x-display-input value="{{ $selectedCustomer?->formatted_date_of_birth }}" />
    </div>
    {{-- Tax ID --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Codice Fiscale</flux:label>
        <x-display-input value="{{ $selectedCustomer?->tax_id }}" />
    </div>
    {{-- Email --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Email</flux:label>
        <x-display-input value="{{ $selectedCustomer?->email }}" />
    </div>
    {{-- Address --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Indirizzo</flux:label>
        <x-display-input value="{{ $selectedCustomer?->address }}" />
    </div>

    <div class="grid grid-cols-3 gap-6">
        {{-- Postal Code --}}
        <div class="flex flex-col gap-1.5 col-span-1">
            <flux:label>Cap</flux:label>
            <x-display-input value="{{ $selectedCustomer?->postal_code }}" />
        </div>
        {{-- Province --}}
        <div class="flex flex-col gap-1.5 col-span-2">
            <flux:label>Provincia</flux:label>
            <x-display-input value="{{ $selectedCustomer?->state }}" />
        </div>
    </div>

    {{-- City --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Città</flux:label>
        <x-display-input value="{{ $selectedCustomer?->city }}" />
    </div>

    {{-- Team Member Select --}}
    @if (auth()->user()->can('assign customer to user'))
        <div class="flex flex-col gap-1.5">
            <flux:label>Collaboratore</flux:label>
            <x-display-input value="{{ $selectedCustomer?->user?->full_name }}" />
        </div>
    @endif
</div>
