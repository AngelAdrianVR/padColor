<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportCost extends Model
{
    use HasFactory;

    protected $table = 'import_costs';

    protected $fillable = [
        'import_id',
        'concept',
        'amount',
        'currency',
        'pendent_amount',
    ];


    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function payments()
    {
        return $this->hasMany(ImportPayment::class);
    }
}
