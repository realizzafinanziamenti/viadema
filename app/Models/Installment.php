<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $fillable = ['value'];

    protected $casts = [
        'value' => 'integer',
    ];

    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }
}
