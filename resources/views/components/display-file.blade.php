@props(['attachment'])

<div
    class="w-full flex justify-between items-center truncate pe-3 py-1.5 ps-4.5 leading-[1.125rem] h-8 rounded-md text-sm bg-white border border-zinc-200 text-zinc-500">

    <button class="cursor-pointer hover:text-azure-custom hover:underline" wire:click='download({{ $attachment->id }})'>
        {{ $attachment->file_name }}
    </button>

    <div wire:click='selectAttachmentForDelete({{ $attachment->id }})'
        class="shrink-0 text-red-600 bg-gray-custom-1 rounded-full flex items-center justify-center cursor-pointer size-6.5 hover:text-white hover:bg-red-600">
        <x-icons.icon-akar-trash-can class="size-4" />
    </div>
</div>
