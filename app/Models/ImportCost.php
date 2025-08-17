<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_id',
        'concept',
        'amount',
        'currency',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}