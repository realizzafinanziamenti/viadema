<div>
    <x-page-title label="Modulistica" class="mt-1" />

    <x-card>
        {{-- Filters and Create Button --}}
        <div class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-4">
                <flux:input class="w-sm! 2xl:w-lg!" wire:model.live.debounce.500ms='search'
                    icon:trailing="magnifying-glass" placeholder="Cerca..." />

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
            @foreach ($formDocuments as $document)
                <div class="h-[180px] border border-gray-custom-2 flex flex-col">
                    <div class="bg-blue-custom-light rounded-sm flex items-center justify-center flex-1">
                        <x-icons.icon-akar-paper
                            class="size-17 cursor-pointer transition-transform duration-200 ease-in-out hover:scale-110"
                            fill="#004CA4" stroke="#effafe" wire:click='download({{ $document->id }})' />
                    </div>

                    <div class="h-12 border-t border-gray-custom-2 p-2 flex items-center justify-between gap-4">
                        <div class="truncate">
                            <div class="text-xs font-semibold text-gray-custom-5 truncate"
                                title="{{ $document->title }}">{{ $document->title }}</div>
                            <div class="text-xs text-gray-custom-5 truncate" title="{{ $document->description }}">
                                {{ $document->description }}</div>
                        </div>

                        <div wire:click='selectDocumentForDelete({{ $document->id }})'
                            class="shrink-0 text-red-600 rounded-full flex items-center justify-center cursor-pointer size-6.5 hover:text-white hover:bg-red-600">
                            <x-icons.icon-akar-trash-can class="size-4" />
                        </div>
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
                        <flux:textarea label="Note" resize="none" wire:model='description' />
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
    </x-card>

    {{-- Delete Document Modal --}}
    <x-delete-modal name="delete-document" header="Conferma Eliminazione Documento" function="deleteDocument"
        message="Sei sicuro di voler eliminare <strong>{{ $selectedDocument?->title }}</strong>?" />
</div>
