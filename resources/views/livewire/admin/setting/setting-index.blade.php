<div>
    <x-page-title label="Impostazioni" class="mt-1" />

    <div class="flex flex-col gap-5">

        {{-- Practice Import --}}
        @can('import practices')
            <livewire:admin.setting.practice-import />
        @endcan

        {{-- Product Subtype Manager --}}
        <livewire:admin.setting.product-subtype-manager />

        {{-- Installment Manager --}}
        <livewire:admin.setting.installment-manager />

        {{-- Financial Table Manager --}}
        <livewire:admin.setting.financial-table-manager />

        {{-- Insurance Manager --}}
        <livewire:admin.setting.insurance-manager />

        {{-- Customer Type Manager --}}
        <livewire:admin.setting.customer-type-manager />

    </div>
</div>
