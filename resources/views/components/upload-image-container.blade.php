@props(['model' => null, 'acceptedFileTypes' => 'default', 'hasError' => false, 'profilePhotoRemoved' => null])

@php
    $errorClass = $hasError
        ? 'border-red-600 bg-red-50 text-red-600'
        : 'border-gray-custom-3 bg-[#F1F0EF] text-gray-custom-5';
@endphp

<div x-data="{
    isDragging: false,

    handleDrop(e) {
        this.isDragging = false;

        const droppedFiles = e.dataTransfer.files;
        $refs.input.files = droppedFiles;
        $refs.input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}" @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
    @drop.prevent="handleDrop($event)" class="w-full">

    <input id="file" name="file" type="file" x-ref="input"
        {{ $attributes->merge([
            'wire:model.live' => $model,
            'class' => 'hidden',
            'accept' => $this->acceptedFileTypes($acceptedFileTypes),
        ]) }} />

    <label for="file" class="cursor-pointer relative">
        <div :class="[
            isDragging ?
            'border-blue-custom-hover bg-blue-custom-light text-blue-custom' :
            'border-gray-custom-3 bg-[#F1F0EF] text-gray-custom-5',
            {{ $hasError ? "'border-red-600 bg-red-50 text-red-600'" : "''" }}
        ]"
            class="relative w-40 h-40 flex justify-center items-center border border-dashed rounded-full border-gray-custom-3 bg-[#F1F0EF]">

            {{-- Camera icon --}}
            <div wire:loading.remove wire:target="{{ $model }}">
                <flux:icon.camera class="size-10" />
            </div>

            {{-- Loading spinner --}}
            <div wire:loading wire:target="{{ $model }}">
                <flux:icon.loading class="size-10" />
            </div>

            {{-- Restore photo button --}}
            @if (auth()->user()->profile_photo_path && $profilePhotoRemoved)
                <x-button-restore-image wire:click="restoreProfilePhoto" class="absolute top-2 right-2" />
            @endif

        </div>
    </label>
</div>
