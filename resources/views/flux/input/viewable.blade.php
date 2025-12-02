@php
    $attributes = $attributes->merge([
        'variant' => 'subtle',
        'class' => '-me-1',
        'square' => true,
        'size' => null,
    ]);
@endphp

<flux:button :$attributes :size="$size === 'sm' || $size === 'xs' ? 'xs' : 'sm'" x-data="{ open: false }"
    x-on:click="open = ! open; $el.closest('[data-flux-input]').querySelector('input').setAttribute('type', open ? 'text' : 'password')"
    x-bind:data-viewable-open="open" aria-label="{{ __('Toggle password visibility') }}" {{-- We need to make the input type "durable" (immune to Livewire morph manipulations): --}}
    x-init="let input = $el.closest('[data-flux-input]')?.querySelector('input');
    
    if (!input) return;
    
    let observer = new MutationObserver(() => {
        let type = open ? 'text' : 'password';
        if (input.getAttribute('type') === type) return;
        input.setAttribute('type', type)
    });
    
    observer.observe(input, { attributes: true, attributeFilter: ['type'] });">
    <x-icons.icon-akar-eye-slashed :width="$size === 'sm' || 'xs' ? '18' : '24' " :height="$size === 'sm' || 'xs' ? '18' : '24'" class="hidden [[data-viewable-open]>&]:block" />
    <x-icons.icon-akar-eye :width="$size === 'sm' || 'xs' ? '18' : '24'" :height="$size === 'sm' || 'xs' ? '18' : '24'" class="block [[data-viewable-open]>&]:hidden" />
</flux:button>
