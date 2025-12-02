<?php

namespace App\Observers;

use App\Models\Installment;
use App\Models\ProductType;
use Demo\Product;

class InstallmentObserver
{
    /**
     * Handle the Installment "created" event.
     */
    public function created(Installment $installment): void
    {
        // Attach the newly created installment to all product types
        foreach (ProductType::all() as $product) {
            $product->installments()->attach($installment->id);
        }
    }

    /**
     * Handle the Installment "updated" event.
     */
    public function updated(Installment $installment): void
    {
        //
    }

    /**
     * Handle the Installment "deleted" event.
     */
    public function deleted(Installment $installment): void
    {
        //
    }

    /**
     * Handle the Installment "restored" event.
     */
    public function restored(Installment $installment): void
    {
        //
    }

    /**
     * Handle the Installment "force deleted" event.
     */
    public function forceDeleted(Installment $installment): void
    {
        //
    }
}
