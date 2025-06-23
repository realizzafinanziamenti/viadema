<div class="grid grid-cols-2 gap-6">
    {{-- First Name --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Nome *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='form.firstName' />
            <flux:error name="form.firstName" />
        </div>
    </div>
    {{-- Last Name --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Cognome *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='form.lastName' />
            <flux:error name="form.lastName" />
        </div>
    </div>
    {{-- Phone --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Cellulare</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='form.phone' />
            <flux:error name="form.phone" />
        </div>
    </div>
    {{-- Tax ID --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Codice Fiscale</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='form.taxId' />
            <flux:error name="form.taxId" />
        </div>
    </div>
    {{-- City --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Città</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input size="sm" wire:model='form.city' />
            <flux:error name="form.city" />
        </div>
    </div>
    {{-- Email --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Email *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input type="email" size="sm" wire:model='form.email' />
            <flux:error name="form.email" />
        </div>
    </div>
    {{-- Password --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Password *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input type="password" size="sm" wire:model='form.password' viewable />
            <flux:error name="form.password" />
        </div>
    </div>
    {{-- Password Confirmation --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Conferma Password *</flux:label>
        <div class="flex flex-col gap-0.5">
            <flux:input type="password" size="sm" wire:model='form.passwordConfirmation' viewable />
            <flux:error name="form.passwordConfirmation" />
        </div>
    </div>
    {{-- Department or Role --}}
    <div class="flex flex-col gap-1.5">
        <flux:label>Dipartimento *</flux:label>
        <div class="flex flex-col gap-0.5">
            <x-dropdown-select size="sm" :selectable-items="$departments" :selected="$department" placeholder='Seleziona dipartimento'
                setFunction="setDepartment" :has-error="$errors->has('form.department')" />

            <flux:error name="form.department" />
            <flux:error name="form.role" />
        </div>
    </div>
    {{-- Profile Photo --}}
    <div class="flex flex-col gap-1.5 col-span-2">
        <flux:label>Immagine Profilo</flux:label>
        <x-filepond::upload wire:model="form.profilePhoto" maxFileSize='4MB'
            accepted-file-types="image/jpeg,image/png" />
    </div>
</div>
