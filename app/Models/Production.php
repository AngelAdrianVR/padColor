<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'client',
        'season',
        'station',
        'quantity',
        'materials',
        'notes',
        'product_id',
        'machine_id',
    ];

    protected $casts = [
        'materials' => 'array',
    ];
}
