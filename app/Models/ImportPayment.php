<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportPayment extends Model
{
    use HasFactory;

    protected $table = 'import_payments';

    protected $fillable = [
        'import_cost_id',
        'amount',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function importCost()
    {
        return $this->belongsTo(ImportCost::class);
    }
}
