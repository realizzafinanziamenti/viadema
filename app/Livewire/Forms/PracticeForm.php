<?php

namespace App\Livewire\Forms;

use App\Models\Practice;
use Livewire\Attributes\Validate;
use Livewire\Form;

class PracticeForm extends Form
{
    public ?Practice $practice = null;

    public $productTypeId = null;
    public $productSubtypeId = null;
    public $userId = null;
    public $customerId = null;
    public $financialTableId = null;
    public $insuranceId = null;
    public $installmentId = null;
    public $customerTypeId = null;
    public $amountDisbursed = null;
    public $totalAmount = null;
    public $rateAmount = null;
    public $tan = null;
    public $teg = null;
    public $taeg = null;
    public $insertedAt = null;
    public $startedAt = null;
    public $paidAt = null;
    public $firstDueDate = null;
    public $lastDueDate = null;
    public $extinguishedAt = null;
    public $renewableAt = null;
    public $practiceStatus = null;
    public $daysTransformation = null;
    public $sumDecPlus35 = null;
    public $previousFinance = null;
    public $practiceCode = null;
    public $notes = null;
}
