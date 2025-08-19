<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RawMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'measure_unit',
        'stock',
        'attributes',
    ];

    protected $casts = [
        'attributes' => 'array', // Importante para la columna JSON
    ];

    public function imports(): BelongsToMany
    {
        return $this->belongsToMany(Import::class, 'import_raw_material')
            ->withPivot('quantity', 'unit_cost', 'currency')
            ->withTimestamps();
    }
}