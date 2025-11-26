<?php

namespace App\Traits;

use Livewire\Attributes\Computed;

trait WithBulkSelection
{
    public array $selected = [];           // IDs selezionati

    /* Seleziona / deseleziona una singola riga */
    public function toggleSelection(int $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    /* Verifica se un singolo ID è selezionato */
    public function isSelected(int $id): bool
    {
        return in_array($id, $this->selected);
    }

    /**
     * Computed: restituisce gli ID della pagina corrente.
     * Richiede che il componente implementi: rows() come computed.
     */
    #[Computed]
    public function currentPageIds(): array
    {
        if (!property_exists($this, 'rows') && !method_exists($this, 'rows')) {
            throw new \Exception("Il componente deve implementare una computed rows() che ritorna un paginator.");
        }

        return $this->rows->getCollection()->pluck('id')->toArray();
    }

    /* Computed: TRUE se tutta la pagina è selezionata */
    #[Computed]
    public function isPageFullySelected(): bool
    {
        $pageIds = array_map('intval', $this->currentPageIds);
        $selected = array_map('intval', $this->selected);

        if (empty($pageIds)) {
            return false;
        }

        return collect($pageIds)
            ->every(fn($id) => in_array($id, $selected));
    }

    /* Seleziona o deseleziona l'intera pagina corrente */
    public function toggleSelectPage(): void
    {
        $pageIds = $this->currentPageIds;

        if ($this->isPageFullySelected) {
            // deseleziona pagina
            $this->selected = array_values(array_diff($this->selected, $pageIds));
        } else {
            // seleziona pagina
            $this->selected = array_unique(array_merge($this->selected, $pageIds));
        }
    }

    /* Seleziona tutti i risultati della query filtrata */
    public function selectAllResults(): void
    {
        $this->selected = $this->query()->pluck('id')->toArray();
    }

    /* Deseleziona completamente tutti gli elementi selezionati */
    public function clearSelection(): void
    {
        if (!method_exists($this, 'query')) {
            return;
        }

        $this->selected = [];
    }

    /* Computed: conta gli elementi selezionati */
    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selected);
    }

    /* Computed: TRUE se c'è almeno una selezione */
    #[Computed]
    public function hasSelection(): bool
    {
        return $this->selectedCount > 0;
    }
}
