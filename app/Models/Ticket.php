<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'ticket_type',
        'expired_date',
        'user_id',
        'responsible_id',
        'category_id',
        'branch',
        'solution_minutes',
        'opened_at',
        'closed_at',
        'paused_at',
    ];

    protected $casts = [
        'expired_date' => 'date',
        'opened_at' => 'datetime',
        'closeed_at' => 'datetime',
        'paused_at' => 'datetime',
    ];

    //relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function ticketSolutions(): HasMany
    {
        return $this->hasMany(TicketSolution::class);
    }

    public function ticketHistories(): HasMany
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function getSolutionMinutes()
    {
        // Calcular el tiempo transcurrido en minutos desde la pausa (en caso de estar en ella) u opened_at hasta ahora
        if ($this->paused_at) {
            $elapsedMinutes = 0;
        } else {
            $elapsedMinutes = now()->diffInMinutes($this->opened_at);
        }

        // Sumar los minutos transcurridos y los adicionales
        $totalMinutes = $elapsedMinutes + $this->solution_minutes;

        return $totalMinutes;
    }
}
