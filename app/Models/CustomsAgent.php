<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomsAgent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'user_id',
    ];

    public function imports(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}