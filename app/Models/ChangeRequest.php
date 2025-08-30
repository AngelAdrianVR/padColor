<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'product_id',
        'status',
        'data_before',
        'data_after',
        'justification',
        'rejection_reason',
        'approved_at',
    ];

    protected $casts = [
        'data_before' => 'array',
        'data_after' => 'array',
        'approved_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ChangeRequestVote::class);
    }
}
