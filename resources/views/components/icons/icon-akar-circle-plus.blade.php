@props(['name' => null, 'default' => ''])

<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
    stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => $default]) }}>
    <path d="M12 8v4m0 0v4m0-4h4m-4 0H8" />
    <circle cx="12" cy="12" r="10" />
</svg>
