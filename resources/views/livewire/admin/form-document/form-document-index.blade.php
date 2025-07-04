<div>
    <x-page-title label="Modulistica" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between gap-4 mb-10">
            <div class="flex items-center gap-4 flex-1">
                <div class="w-full max-w-md 2xl:max-w-lg!">
                    <flux:input wire:model.live.debounce.500ms='search' icon:trailing="magnifying-glass"
                        placeholder="Cerca..." />
                </div>

                <div class="w-48">
                    <flux:input type="date" wire:model.live='filterDate' />
                </div>
            </div>

            @can('create form documents')
                <x-buttons.create-button label="Carica documento" wire:click='openCreateDocumentModal' />
            @endcan
        </div>

        <div class="grid grid-cols-4 xl:grid-cols-5 gap-8 mb-5">
            {{-- Loop through form documents --}}
            @foreach ($formDocuments as $index => $document)
                <div class="h-[180px] border border-gray-custom-2 flex flex-col">
                    <div class="bg-blue-custom-light rounded-sm flex items-center justify-center flex-1">
                        <x-icons.icon-akar-paper
                            class="size-17 transition-transform duration-200 ease-in-out hover:scale-110" fill="#004CA4"
                            stroke="#effafe" :title="$document->description" />
                    </div>

                    <div class="h-12 border-t border-gray-custom-2 p-2 flex items-center justify-between gap-4">
                        <div class="truncate">
                            <div class="text-xs font-semibold text-gray-custom-5 truncate"
                                title="{{ $document->title }}">{{ $document->title }}</div>
                            <div class="text-xs text-gray-custom-5 truncate">
                                {{ $document->formatted_created_at }}</div>
                        </div>

                        @php
                            // Setta top per gli ultimi 5 elementi per pagina (fila di sotto)
                            $align = $index >= count($formDocuments) - 5 ? 'top' : 'right';
                        @endphp

                        <x-dropdown :align="$align" width="w-30">
                            <x-slot name="trigger">
                                <div
                                    class="shrink-0 text-gray-custom-5 rounded-full flex items-center justify-center cursor-pointer size-6.5">
                                    <x-icons.icon-akar-more class="size-4" />
                                </div>
                            </x-slot>

                            <x-slot name="content">
                                @can('download form documents')
                                    <x-dropdown-button class="cursor-pointer rounded-t-md"
                                        wire:click='download({{ $document->id }})'>
                                        Scarica
                                    </x-dropdown-button>
                                @endcan

                                @can('update form documents')
                                    <x-dropdown-button class="cursor-pointer"
                                        wire:click='selectDocumentForUpdate({{ $document->id }})'>
                                        Rinomina
                                    </x-dropdown-button>
                                @endcan

                                @can('delete form documents')
                                    <x-dropdown-button class="cursor-pointer rounded-b-md"
                                        wire:click='selectDocumentForDelete({{ $document->id }})'>
                                        Elimina
                                    </x-dropdown-button>
                                @endcan
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination buttons --}}
        {{ $formDocuments->links() }}

        {{-- Create New Document Modal --}}
        <x-modal name="document-create" maxWidth="xl">
            <x-modal-header label="Carica documento" />

            <form wire:submit.prevent='store' class="w-full mt-10 mb-5">
                <div class="flex flex-col gap-3">
                    {{-- Title --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Titolo *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='title' />
                            <flux:error name="title" />
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:textarea label="Descrizione" resize="none" wire:model='description' />
                        <flux:error name="description" />
                    </div>

                    {{-- New Attachments --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Allegato *</flux:label>
                        <x-filepond::upload wire:model="file" max-file-size="10MB"
                            accepted-file-types="{{ $this->acceptedFileTypes(['documents', 'excel']) }}"
                            allow-image-preview="false" />
                        <flux:error name="file" />

                        <div class="text-xs text-gray-custom-4">
                            <div>- Max 10MB</div>
                            <div>- Formati accettati: pdf, doc, docx, xls, xlsm, xlsx, csv</div>
                        </div>
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex items-center justify-end gap-x-3 mt-18">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'document-create')"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>

                    <flux:button variant="primary" type="submit" size="sm"
                        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                        Carica
                    </flux:button>
                </div>
            </form>
        </x-modal>

        {{-- Edit Document Modal --}}
        <x-modal name="document-edit" maxWidth="xl">
            <x-modal-header label="Rinomina documento" />

            <form wire:submit.prevent='edit' class="w-full mt-10 mb-5">
                <div class="flex flex-col gap-3">
                    {{-- Title --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:label>Titolo *</flux:label>
                        <div class="flex flex-col gap-0.5">
                            <flux:input size="sm" wire:model='title' />
                            <flux:error name="title" />
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="flex flex-col gap-1.5">
                        <flux:textarea label="Descrizione" resize="none" wire:model='description' />
                        <flux:error name="description" />
                    </div>
                </div>

                {{-- Submit Buttons --}}
                <div class="flex items-center justify-end gap-x-3 mt-18">
                    <flux:button variant="primary" type="button" size="sm"
                        x-on:click="$dispatch('close-modal', 'document-edit')"
                        class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                        Annulla
                    </flux:button>

                    <flux:button variant="primary" type="submit" size="sm"
                        class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                        Modifica
                    </flux:button>
                </div>
            </form>
        </x-modal>
    </x-card>

    {{-- Delete Document Modal --}}
    <x-delete-modal name="delete-document" header="Conferma Eliminazione" function="deleteDocument"
        message="Sei sicuro di voler eliminare <strong>{{ $selectedDocument?->title }}</strong>?" />
</div>
