<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-5" label="Import pratiche da excel" />

        <form wire:submit.prevent='import' class="flex flex-col gap-1.5 items-start">

            <input type="file" id="fileUpload" wire:model.live="file" class="hidden" accept=".xlsx,.xls" />

            <label for="fileUpload"
                class="flex items-center justify-center w-44 h-8 cursor-pointer text-sm rounded-md bg-black-custom border-black-custom
                text-white hover:bg-zinc-700 hover:border-zinc-700">

                <div class="flex items-center gap-2" wire:loading.remove>
                    <x-icons.icona-importa />
                    Importa da Excel
                </div>

                <div wire:loading class="text-white">
                    <flux:icon.loading class="size-4" />
                </div>
            </label>

            @if ($file)
                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                    File selezionato: {{ $file->getClientOriginalName() }}
                </div>
            @endif

            @if ($isImporting)
                <div class="text-sm text-gray-custom-5">
                    Importazione in corso...
                </div>
            @endif

            <flux:error name="file" />

        </form>
    </x-card>
</div>
