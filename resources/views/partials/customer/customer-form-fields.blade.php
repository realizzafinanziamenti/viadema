<div class="grid grid-cols-2 gap-6">
    {{-- First Name --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Nome *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='{{ $form }}.firstName' />
            <flux:error name="{{ $form }}.firstName" />
        </div>
    </div>

    {{-- Last Name --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Cognome *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='{{ $form }}.lastName' />
            <flux:error name="{{ $form }}.lastName" />
        </div>
    </div>

    @if ($context === 'lead')
        {{-- Customer Type --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Tipologia Cliente</flux:label>
            <div class="flex flex-col gap-0.5">
                <x-dropdown-select size="sm" align="right" :selectable-items="$customerTypes" :selected="$selectedCustomerTypeId"
                    placeholder='Seleziona tipologia cliente' setFunction="setCustomerType" :has-error="$errors->has('{{ $form }}.customerTypeId')" />
                <flux:error name="{{ $form }}.customerTypeId" />
            </div>
        </div>

        {{-- Lead Source --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Canale di acquisizione</flux:label>
            <div class="flex flex-col gap-0.5">
                <x-dropdown-select size="sm" align="right" :selectable-items="$leadSources" :selected="$selectedLeadSource"
                    placeholder='Seleziona canale di acquisizione' setFunction="setLeadSource" :has-error="$errors->has('{{ $form }}.leadSource')" />
                <flux:error name="{{ $form }}.leadSource" />
            </div>
        </div>

        {{-- Lead Status --}}
        <div class="flex flex-col gap-1.5">
            <flux:label>Stato</flux:label>
            <div class="flex flex-col gap-0.5">
                <x-dropdown-select size="sm" align="right" :selectable-items="$leadStatuses" :selected="$selectedLeadStatus"
                    placeholder='Seleziona stato lead' setFunction="setLeadStatus" :has-error="$errors->has('{{ $form }}.leadStatus')" />
                <flux:error name="{{ $form }}.leadStatus" />
            </div>
        </div>
    @endif

    {{-- Phone --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Cellulare *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='{{ $form }}.phone' />
            <flux:error name="{{ $form }}.phone" />
        </div>
    </div>

    {{-- Date of Birth --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Data di Nascita</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input type="date" size="sm" wire:model='{{ $form }}.dateOfBirth' />
            <flux:error name="{{ $form }}.dateOfBirth" />
        </div>
    </div>

    {{-- Tax ID --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Codice Fiscale</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='{{ $form }}.taxId' />
            <flux:error name="{{ $form }}.taxId" />
        </div>
    </div>

    {{-- Email --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Email</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input type="email" size="sm" wire:model='{{ $form }}.email' />
            <flux:error name="{{ $form }}.email" />
        </div>
    </div>

    {{-- Address --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Indirizzo</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='{{ $form }}.address' />
            <flux:error name="{{ $form }}.address" />
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        {{-- Postal Code --}}
        <div class="flex flex-col gap-1.5 col-span-1">
            <flux:label>Cap</flux:label>
            <div class="flex flex-col gap-0.5">
                <flux:input size="sm" wire:model='{{ $form }}.postalCode' />
                <flux:error name="{{ $form }}.postalCode" />
            </div>
        </div>

        {{-- Province --}}
        <div class="flex flex-col gap-1.5 col-span-2">
            <flux:label>Provincia</flux:label>
            <div class="flex flex-col gap-0.5">
                <flux:input size="sm" wire:model='{{ $form }}.state' />
                <flux:error name="{{ $form }}.state" />
            </div>
        </div>
    </div>

    {{-- City --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Città</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='{{ $form }}.city' />
            <flux:error name="{{ $form }}.city" />
        </div>
    </div>

    {{-- Team Member Select --}}
    @if (
        (auth()->user()->can('assign customer to user') && $context === 'customer') ||
            (auth()->user()->can('assign lead to user') && $context === 'lead'))
        <div class="flex flex-col gap-1.5">
            <flux:label>Collaboratore *</flux:label>

            <x-dropdown-select size="sm" align="top" :selectable-items="$teamMembers" :selected="$selectedUserId" searchable
                search="{{ $search }}" placeholder='Seleziona collaboratore' setFunction="setTeamMember"
                :has-error="$errors->has('{{ $form }}.userId')" />

            <flux:error name="{{ $form }}.userId" />
        </div>
    @endif

    {{-- Notes --}}
    <div class="flex flex-col gap-1.5 col-span-2">
        <flux:textarea label="Note" resize="none" wire:model='{{ $form }}.notes' />
        <flux:error name="{{ $form }}.notes" />
    </div>
</div>
