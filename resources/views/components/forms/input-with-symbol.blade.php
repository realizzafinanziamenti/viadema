@props(['symbol' => '', 'placeholder' => null, 'size' => 'sm', 'type' => 'text', 'disabled' => false])

<flux:input type="{{ $type }}" size="{{ $size }}" :placeholder="$placeholder" :disabled="$disabled"
    {{ $attributes->merge(['step' => '', 'min' => '', 'max' => '']) }}>
    @if ($symbol)
        <x-slot:iconLeading>
            <span class="text-gray-400 text-sm ps-1">{{ $symbol }}</span>
        </x-slot:iconLeading>
    @endif
</flux:input>
