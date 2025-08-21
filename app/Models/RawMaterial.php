<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'user_id',
    ];

    protected $casts = [
        'attributes' => 'array', // Importante para la columna JSON
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function imports(): BelongsToMany
    {
        return $this->belongsToMany(Import::class, 'import_raw_material')
            ->withPivot('quantity', 'unit_cost')
            ->withTimestamps();
    }
}
