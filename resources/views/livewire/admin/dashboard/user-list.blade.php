<x-dashboard.dashboard-card class="col-span-24 xl:col-span-13 h-[300px] flex flex-col gap-3" header="Lista collaboratori">

    <x-table class="my-1" minWidth="min-w-[500px]">
        {{-- Table header --}}
        <x-slot name="header" class="border-b">
            <x-table-header height="h-8" label="ID" class="w-[80px]" />
            <x-table-header height="h-8" label="Nome collaboratore" class="w-2/4" />
            <x-table-header height="h-8" label="Comparto" class="w-1/4" />
            <x-table-header height="h-8" label="Pratiche completate" class="w-1/4 text-center" />
            <x-table-header height="h-8" class="w-[50px]">
                {{-- Actions --}}
            </x-table-header>
        </x-slot>

        {{-- Table body --}}
        @foreach ($users as $teamMember)
            <tr wire:key='{{ $teamMember->id }}' class="border-y border-collapse">
                <x-table-data height="h-10" label="{{ $teamMember->id }}" />

                <x-table-data height="h-10" class="inline-flex items-center">
                    <x-user-table-data size="8" :user="$teamMember" />
                </x-table-data>

                <x-table-data height="h-10" truncate
                    class="uppercase font-semibold {{ $teamMember->profile?->user_department?->getLabelColor() }}"
                    label="{{ $teamMember->profile?->user_department?->getLabelText() }}" />

                <x-table-data height="h-10" truncate label="{{ $teamMember->disbursed_practices_count }}"
                    class="text-center" />

                {{-- Actions --}}
                <x-table-data height="h-10" class="inline-flex items-center justify-end w-full gap-3">
                    @can('view', $teamMember)
                        <a href="{{ route('user.show', ['id' => $teamMember->id]) }}" wire:navigate>
                            <x-table-action-button-view />
                        </a>
                    @endcan
                </x-table-data>
            </tr>
        @endforeach
    </x-table>

</x-dashboard.dashboard-card>
