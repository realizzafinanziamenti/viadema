@props(['type' => 'button', 'label' => '', 'section' => '', 'hasError' => false])

<button type="{{ $type }}" @click="section = '{{ $section }}'"
    :class="section === '{{ $section }}' ? 'text-pink-custom font-bold border-b-2 border-pink-custom' : 'text-gray-custom-3'"
    {{ $attributes->merge([
        'class' => 'text-sm cursor-pointer',
    ]) }}>{{ $label }}</button>
