 @props(['notification', 'index'])

 {{-- Title custom css based on notification type --}}
 @php
     $titleCss = match ($notification->data['type']) {
         'practice-renewability-alert' => 'text-red-600',
         default => 'text-azure-custom',
     };

     $bgCss = match ($notification->data['type']) {
         'practice-renewability-alert' => 'bg-red-100/80 hover:bg-red-100/50',
         default => 'bg-azure-custom-25 hover:bg-azure-custom-20',
     };
 @endphp

 {{-- Notifica --}}
 <div wire:click="redirectTo('{{ $notification->id }}')" wire:key="{{ $notification->id }}"
     class="{{ $notification->read_at ? 'bg-white hover:bg-gray-custom-1' : $bgCss }}
        text-gray-custom-5 text-[13px] px-3.5 py-2.5 flex justify-between gap-4 cursor-pointer
        {{ $this->isLastOfRange($index) ? 'mb-4' : 'mb-2' }}">

     {{-- Left --}}
     <div class="flex-1 flex flex-col gap-1 truncate">
         <div class="font-bold text-sm {{ $titleCss }} truncate">
             {{ $notification->data['title'] }}
         </div>

         <div title="{{ $notification->data['message'] }}" class="truncate">{{ $notification->data['message'] }}</div>
     </div>

     {{-- Right --}}
     <div class="shrink-0 flex flex-col justify-between items-end">
         <span class="text-[11px] text-gray-custom-4">
             {{ $notification->created_at->diffForHumans() }}
         </span>

         <x-dropdown>
             <x-slot name="trigger">
                 <flux:icon name="ellipsis-horizontal" />
             </x-slot>

             <x-slot name="content">
                 <div class="overflow-y-auto max-h-56">
                     @if (!$notification->read_at)
                         <x-dropdown-button class="cursor-pointer" wire:click="markAsRead('{{ $notification->id }}')">
                             Segna come letto
                         </x-dropdown-button>
                     @endif

                     <x-dropdown-button class="cursor-pointer"
                         wire:click="deleteNotification('{{ $notification->id }}')">
                         Elimina
                     </x-dropdown-button>
                 </div>
             </x-slot>
         </x-dropdown>
     </div>
 </div>
