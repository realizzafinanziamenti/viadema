@props(['user', 'size' => '10'])

<div class="inline-flex items-center gap-2.5">
    {{-- Profile photo --}}
    <img class="object-cover w-{{ $size }} h-{{ $size }} border rounded-full shrink-0"
        src="{{ $user?->getProfilePhotoUrl() }}" alt="Immagine Profilo Utente">

    <span class="truncate" title="{{ $user?->full_name }}">{{ $user?->full_name }}</span>
</div>
