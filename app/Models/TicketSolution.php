<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TicketSolution extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'description',
        'user_id',
        'ticket_id',
    ]; 
    
    //relationships
    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket() :BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
