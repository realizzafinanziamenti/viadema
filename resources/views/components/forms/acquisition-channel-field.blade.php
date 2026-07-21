@props([
    'options',
    'selected' => null,
    'setFunction' => 'setOpportunityAcquisitionChannel',
    'errorName' => 'opportunityForm.acquisitionChannel',
])

<div {{ $attributes->class(['flex flex-col gap-1.5']) }}>
    <flux:label>Canale di acquisizione</flux:label>

    <x-dropdown-select
        size="sm"
        align="right"
        :selectable-items="$options"
        :selected="$selected"
        placeholder="Seleziona canale di acquisizione"
        setFunction="{{ $setFunction }}"
        :has-error="$errors->has($errorName)"
    />

    <flux:error name="{{ $errorName }}" />
</div>
