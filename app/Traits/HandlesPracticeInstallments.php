<?php

namespace App\Traits;

use App\Models\InstallmentProductDefault;
use Carbon\Carbon;
use Livewire\Form;

trait HandlesPracticeInstallments
{
    /**
     * Recalculate the last installment date based on the first installment date and the selected installment
     */
    public function recalculateLastInstallmentDate(Form $form, array $installments): void
    {
        if ($form->firstInstallmentDate && $form->installmentId) {
            // Get the total number of installments for the selected installment
            $totalInstallments = $installments[$form->installmentId] ?? null;

            if ($totalInstallments) {
                // Calculate the last installment date based on the first installment date and total installments
                $firstDate = Carbon::parse($form->firstInstallmentDate);
                $lastDate = $firstDate->copy()->addMonthsNoOverflow($totalInstallments - 1);
                // Set the last installment date in the practice form
                $form->lastInstallmentDate = $lastDate->format('Y-m-d');
            }
        }
    }

    /**
     * Set renewability and alert percentage based on the selected product type and installment
     */
    public function setRenewabilityAndAlertPercentage(Form $form): void
    {
        if ($form->productTypeId && $form->installmentId) {
            $default = InstallmentProductDefault::where('product_type_id', $form->productTypeId)
                ->where('installment_id', $form->installmentId)
                ->first();

            if ($default) {
                $form->renewabilityPercentage = $default->renewability_percentage;
                $form->percentageAlert = $default->percentage_alert;
            }
        }
    }

    /**
     * Recalculate the renewability date based on the first installment date and renewability percentage
     */
    public function recalculateRenewabilityDate(Form $form): void
    {
        if ($form->firstInstallmentDate && $form->renewabilityPercentage && $form->installmentId) {
            // Parse the first installment date
            $firstInstallmentDate = Carbon::parse($form->firstInstallmentDate);
            // Get the total number of installments for the selected installment
            $totalInstallments = $this->installments[$form->installmentId] ?? null;

            if ($totalInstallments) {
                // Calculate the renewability installments based on the renewability percentage
                $renewabilityInstallments = ceil($totalInstallments * ($form->renewabilityPercentage / 100));
                // Add the renewability installments to the first installment date
                $renewabilityDate = $firstInstallmentDate->addMonthsNoOverflow($renewabilityInstallments)->format('Y-m-d');
                // Set the renewability date in the practice form
                $form->renewabilityDate = $renewabilityDate;
            }
        }
    }
}
