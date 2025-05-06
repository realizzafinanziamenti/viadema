@props(['user'])

<div class="inline-flex items-center gap-2.5">
    {{-- Profile photo --}}
    <img class="object-cover w-10 h-10 border rounded-full shrink-0" src="{{ $user->getProfilePhotoUrl() }}"
        alt="Immagine Profilo Utente">

    <span class="truncate" title="{{ $user->full_name }}">{{ $user->full_name }}</span>
</div>
