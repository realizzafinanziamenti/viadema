@props(['label' => null, 'labelTextColor' => 'text-gray-custom-4'])

<div class="inline-flex items-center">
    <label class="flex items-center cursor-pointer">
        <div class="relative flex-shrink-0 flex items-center justify-center">
            <input type="checkbox"
                {{ $attributes->merge([
                    'class' =>
                        'peer h-3 w-3 cursor-pointer transition-all appearance-none rounded-xs bg-white border border-gray-300 checked:bg-azure-custom checked:border-azure-custom',
                ]) }} />

            <span
                class="absolute text-white opacity-0 peer-checked:opacity-100 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                <flux:icon.check class="size-3" />
            </span>
        </div>

        @if ($label)
            <span class="ml-2 text-[13px] {{ $labelTextColor }} font-medium select-none">
                {{ $label }}
            </span>
        @endif
    </label>
</div>
