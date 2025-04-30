<div class="w-full">
    <x-card class="w-3xl mx-auto">
        <x-card-header label="Modifica cliente" />

        <form wire:submit.prevent='save' class="w-2xl mx-auto mt-10 mb-5">
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
                    <flux:label>Cellulare *</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.phone' />
                        <flux:error name="form.phone" />
                    </div>
                </div>
                {{-- Date of Birth --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Data di Nascita</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="date" size="sm" wire:model='form.dateOfBirth' />
                        <flux:error name="form.dateOfBirth" />
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
                {{-- Email --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Email</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input type="email" size="sm" wire:model='form.email' />
                        <flux:error name="form.email" />
                    </div>
                </div>
                {{-- Address --}}
                <div class="flex flex-col gap-1.5">
                    <flux:label>Indirizzo</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <flux:input size="sm" wire:model='form.address' />
                        <flux:error name="form.address" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    {{-- Postal Code --}}
                    <div class="flex flex-col gap-1.5 col-span-1">
                        <flux:label>Cap</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='form.postalCode' />
                            <flux:error name="form.postalCode" />
                        </div>
                    </div>
                    {{-- Province --}}
                    <div class="flex flex-col gap-1.5 col-span-2">
                        <flux:label>Provincia</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='form.state' />
                            <flux:error name="form.state" />
                        </div>
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
                {{-- Team Member Select --}}
                @if (auth()->user()->can('assign customer to user'))
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Collaboratore</flux:label>

                        <select placeholder="Seleziona" wire:model='form.userId'>
                            <option value="">Seleziona un collaboratore</option>
                            @foreach ($teamMembers as $teamMember)
                                <option wire:key='{{ $teamMember->id }}' value="{{ $teamMember->id }}">
                                    {{ $teamMember->full_name }}
                                </option>
                            @endforeach
                        </select>

                        <flux:error name="form.userId" />
                    </div>
                @endif
            </div>

            {{-- Submit Buttons --}}
            <div class="flex items-center justify-end gap-x-3 mt-18">
                <a href="{{ route('customer.index') }}" wire:navigate>
                    <flux:button variant="primary" type="button" size="sm"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>
                </a>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Modifica
                </flux:button>
            </div>
        </form>
    </x-card>
</div>
