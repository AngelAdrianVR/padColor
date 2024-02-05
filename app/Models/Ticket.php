<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ticket extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'expired_date',
        'user_id',
        'responsible_id',
        'category_id',
    ];

    protected $casts = [
        'expired_date' => 'date'
    ];

    //relationships
    public function category() :BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responsible() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}