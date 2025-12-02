@props(['mt' => 'mt-5', 'mb' => 'mb-5', 'minWidth' => 'min-w-[1024px]'])

<div class="w-full overflow-auto">
    <table {{ $attributes->merge(['class' => $minWidth . ' w-full text-sm text-left table-fixed text-black-custom']) }}>
        <thead class="text-sm text-gray-custom-4">
            <tr>
                {{ $header }}
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
