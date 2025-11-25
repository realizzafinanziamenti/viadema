<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Masmerise\Toaster\Toaster;

trait HandlesEntityActions
{
    public function selectEntityForAction(
        int $id,
        string $modelClass,
        string $property = 'selectedEntity',
        string $modalName = 'modal',
        string $notFoundMessage = 'Elemento non trovato'
    ): void {
        try {
            $this->{$property} = $modelClass::findOrFail($id);
        } catch (Exception $e) {
            Toaster::error($notFoundMessage);
            return;
        }

        $this->dispatch('open-modal', $modalName);
    }

    public function deleteSelectedEntity(
        string $property = 'selectedEntity',
        string $modalName = 'delete-modal',
        string $successMessage = 'Elemento eliminato con successo',
    ): void {
        $entity = $this->{$property} ?? null;

        if ($entity instanceof Model) {
            $entity->delete();
            $this->{$property} = null;

            if (method_exists($this, 'resetPage')) {
                $this->resetPage();
            }

            $this->dispatch('close-modal', $modalName);
            Toaster::success($successMessage);
        }
    }
}
