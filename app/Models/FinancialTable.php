<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTable extends Model
{
    protected $fillable = ['percentage'];

    protected $casts = [
        'percentage' => 'decimal:2',
    ];

    // public function Practices()
    // {
    //     return $this->hasMany(Practice::class);
    // }
}
