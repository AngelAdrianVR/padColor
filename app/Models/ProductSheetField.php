<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSheetField extends Model
{
    use HasFactory;

    protected $fillable = [
        'tab_id',
        'section',
        'label',
        'slug',
        'type',
        'options',
        'order',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tab that owns the ProductSheetField
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tab(): BelongsTo
    {
        return $this->belongsTo(ProductSheetTab::class, 'tab_id');
    }

    /**
     * Get all of the options for the ProductSheetField
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fieldOptions(): HasMany
    {
        return $this->hasMany(ProductSheetFieldOption::class, 'field_id');
    }
}
