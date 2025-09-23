<x-modal name="log-details" maxWidth="2xl">
    <div class="flex flex-col">
        <x-modal-header label="Dettagli attività" class="mb-6" />

        @if ($selectedLog)
            {{-- Informazioni base --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <flux:label>Descrizione</flux:label>
                    <p class="text-sm text-black-custom">{{ $selectedLog->description }}</p>
                </div>

                <div>
                    <flux:label>Data e ora</flux:label>
                    <p class="text-sm text-black-custom">{{ $selectedLog->created_at->format('d/m/Y H:i:s') }}</p>
                </div>

                <div>
                    <flux:label>Utente</flux:label>
                    <p class="text-sm text-black-custom">
                        {{ $selectedLog->causer?->full_name ?? 'Sistema' }}
                    </p>
                </div>

                <div>
                    <flux:label>Tipo log</flux:label>
                    <p class="text-sm text-black-custom">{{ $this->formatFieldValue($selectedLog->log_name) }}</p>
                </div>

                {{-- Link alla risorsa (se presente) --}}
                @if ($selectedLog->properties['url'] ?? null)
                    <div>
                        <flux:label>Link alla risorsa</flux:label>
                        <p class="text-sm text-black-custom">
                            <a href="{{ $selectedLog->properties['url'] }}" wire:navigate
                                class="text-azure-custom text-sm underline">
                                Clicca qui per visualizzare
                            </a>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Modifiche ai campi (se presenti) --}}
            @if ($selectedLog->changes()['attributes'] ?? null)
                <div class="mb-6">
                    <flux:label>Campi modificati</flux:label>

                    <div class="bg-gray-custom-1 rounded-lg p-3 space-y-3 max-h-56 overflow-y-auto">
                        {{-- Cicla attraverso i campi modificati --}}
                        @php
                            $translations = $selectedLog->properties['field_translations'] ?? [];
                            $attributes = $selectedLog->changes()['attributes'] ?? [];
                            $old = $selectedLog->changes()['old'] ?? [];
                        @endphp

                        @foreach ($attributes as $field => $newValue)
                            @php
                                $fieldName = $translations[$field] ?? ucfirst(str_replace('_', ' ', $field));
                                $oldValue = $old[$field] ?? null;
                            @endphp

                            <div class="flex flex-col space-y-0.5 text-sm">
                                <div class="text-black-custom text-[13px]">{{ $fieldName }}:</div>

                                <div class="flex items-center space-x-2">
                                    @if ($field === 'password')
                                        <span class="text-xs text-black-custom">
                                            🔒 Campo sensibile modificato
                                        </span>
                                    @else
                                        <span>
                                            {{ $oldValue }}
                                        </span>
                                        <span class="text-gray-custom-5">→</span>
                                        <span>
                                            {{ $newValue }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Messaggio di errore (se presente) --}}
            @if ($selectedLog->properties['error_message'] ?? null)
                <div class="mb-6">
                    <flux:label>Messaggio di errore</flux:label>
                    <div class="bg-red-50 text-red-custom text-sm p-3 rounded-lg max-h-56 overflow-y-auto">
                        {{ $selectedLog->properties['error_message'] }}
                    </div>
                </div>
            @endif
        @endif

        {{-- Button --}}
        <div class="flex gap-3 justify-end mt-6">
            <flux:button variant="primary" type="button" size="sm"
                x-on:click="$dispatch('close-modal', 'log-details')"
                class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                Chiudi
            </flux:button>
        </div>
    </div>
</x-modal>
