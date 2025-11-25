@props([
    'name' => 'import-modal',
    'header' => '',
    'submitFunction' => 'import',
    'show' => false,
    'maxWidth' => 'md',
    'temporaryImportFile' => null,
    'importFile' => null,
    'teamMembers' => null,
    'userId' => null,
    'userSearch' => '',
])

<x-modal :name="$name" :show="$show" maxWidth="{{ $maxWidth }}">
    <div class="flex flex-col">
        <x-modal-header :label="$header" class="mb-6" />

        <form wire:submit.prevent='{{ $submitFunction }}'>
            {{-- Import File Input --}}
            @if ($importFile)
                <div
                    class="w-full flex justify-between items-center truncate pe-3 py-1.5 ps-4.5 leading-[1.125rem] h-8 rounded-md text-sm bg-white border border-zinc-200 text-zinc-500">
                    {{ $importFile->getClientOriginalName() }}

                    <div wire:click='removeImportFile'
                        class="shrink-0 text-red-600 bg-gray-custom-1 rounded-full flex items-center justify-center cursor-pointer size-6.5 hover:text-white hover:bg-red-600">
                        <x-icons.icon-akar-trash-can class="size-4" />
                    </div>
                </div>
            @else
                <div class="flex flex-col gap-1.5">
                    <x-upload-files-container model="temporaryImportFile" :has-error="$errors->has('temporaryImportFile')" acceptedFileTypes="excel" />

                    <flux:error name="temporaryImportFile" />

                    <div class="text-xs text-gray-custom-4">
                        <div>- Max file da 20MB</div>
                        <div>- Formati accettati: xls, xlsx</div>
                    </div>
                </div>
            @endif

            {{-- User --}}
            @if (auth()->user()->can('assign practice to user'))
                <div class="flex flex-col gap-1.5 mt-4">
                    <flux:label>Assegna ad utente</flux:label>
                    <div class="flex flex-col gap-0.5">
                        <x-dropdown-select size="sm" :selectable-items="$teamMembers" :selected="$userId" searchable
                            search="userSearch" placeholder='Assegna collaboratore tramite file'
                            setFunction="setUserForImport" :has-error="$errors->has('userId')" />

                        <flux:error name="userId" />
                    </div>
                </div>
            @endif

            {{-- Buttons --}}
            <div class="flex gap-3 justify-end mt-16">
                <flux:button variant="primary" type="button" size="sm"
                    x-on:click="$dispatch('close-modal', '{{ $name }}')"
                    class="px-10 bg-gray-custom-2 border-gray-custom-2 text-gray-custom-5 hover:bg-gray-custom-3-hover hover:border-gray-custom-3-hover hover:text-white">
                    Annulla
                </flux:button>

                <flux:button variant="primary" type="submit" size="sm"
                    class="px-10 bg-azure-custom border-azure-custom hover:bg-azure-custom-hover hover:border-azure-custom-hover">
                    Importa
                </flux:button>
            </div>
        </form>
    </div>
</x-modal>
