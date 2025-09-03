<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSheetFieldOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_id',
        'label',
        'value',
        'order',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(ProductSheetField::class, 'field_id');
    }
}
