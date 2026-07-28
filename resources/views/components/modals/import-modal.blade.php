@props([
    'name' => 'import-modal',
    'header' => '',
    'submitFunction' => 'import',
    'show' => false,
    'maxWidth' => 'md',
    'temporaryImportFile' => null,
    'importFile' => null,
    'users' => null,
    'userId' => null,
    'userSearch' => '',
    'canAssignUser' => false,
])

<x-modal
    :name="$name"
    :show="$show"
    :maxWidth="$maxWidth"
>
    <div class="flex flex-col">
        <x-modal-header
            :label="$header"
            class="mb-6"
        />

        <form wire:submit.prevent="{{ $submitFunction }}">
            {{-- Import File Input --}}
            @if ($importFile)
                <div
                    class="flex h-8 w-full items-center justify-between truncate rounded-md border border-zinc-200 bg-white py-1.5 pe-3 ps-4.5 text-sm leading-[1.125rem] text-zinc-500"
                >
                    <span class="truncate">
                        {{ $importFile->getClientOriginalName() }}
                    </span>

                    <button
                        type="button"
                        wire:click="removeImportFile"
                        class="flex size-6.5 shrink-0 cursor-pointer items-center justify-center rounded-full bg-gray-custom-1 text-red-600 hover:bg-red-600 hover:text-white"
                        title="Rimuovi file"
                    >
                        <x-icons.icon-akar-trash-can class="size-4" />
                    </button>
                </div>
            @else
                <div class="flex flex-col gap-1.5">
                    <x-upload-files-container
                        model="temporaryImportFile"
                        :has-error="$errors->has('temporaryImportFile')"
                        acceptedFileTypes="excel"
                    />

                    <flux:error name="temporaryImportFile" />

                    <div class="text-xs text-gray-custom-4">
                        <div>- Dimensione massima: 20 MB</div>
                        <div>- Formato accettato: xlsx</div>
                    </div>
                </div>
            @endif

            {{-- User --}}
            @if ($canAssignUser)
                <div class="mt-6 flex flex-col gap-1.5">
                    <flux:label>Assegna ad utente</flux:label>

                    <div class="flex flex-col gap-0.5">
                        <x-dropdown-select
                            size="sm"
                            :selectable-items="$users"
                            :selected="$userId"
                            searchable
                            search="userSearch"
                            placeholder="Assegna collaboratore tramite file"
                            setFunction="setUserForImport"
                            :has-error="$errors->has('userId')"
                        />

                        <flux:error name="userId" />
                    </div>
                </div>
            @endif

            {{-- Buttons --}}
            <div class="mt-16 flex justify-end gap-3">
                <flux:button
                    variant="primary"
                    type="button"
                    size="sm"
                    x-on:click="$dispatch('close-modal', '{{ $name }}')"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white"
                >
                    Annulla
                </flux:button>

                <flux:button
                    variant="primary"
                    type="submit"
                    size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover"
                >
                    Importa
                </flux:button>
            </div>
        </form>
    </div>
</x-modal>
