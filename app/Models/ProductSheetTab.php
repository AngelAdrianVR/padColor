<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSheetTab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'order',
        'is_active',
    ];

    /**
     * Get all of the fields for the ProductSheetTab
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ProductSheetField::class, 'tab_id');
    }
}
