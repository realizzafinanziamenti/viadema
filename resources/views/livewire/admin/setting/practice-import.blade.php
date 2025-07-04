<div>
    <x-card class="max-w-3xl mx-auto">
        <x-card-header class="mb-5" label="Import pratiche da excel" />

        <form wire:submit.prevent='import' class="flex">

            <input type="file" id="fileUpload" wire:model="file" class="hidden">

            <label for="fileUpload"
                class="flex items-center px-6 h-8 cursor-pointer text-sm rounded-md bg-black-custom border-black-custom
                text-white hover:bg-zinc-700 hover:border-zinc-700">

                <div class="flex items-center gap-2" wire:loading.remove>
                    <x-icons.icona-importa />
                    Importa da Excel
                </div>

                <div wire:loading class="text-white">Carico</div>
            </label>

            <flux:error name="file" class="mt-4" />

        </form>
    </x-card>
</div>
