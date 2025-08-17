<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_event_id',
        'notifiable_id',
        'notifiable_type',
    ];

    public function event()
    {
        return $this->belongsTo(NotificationEvent::class, 'notification_event_id');
    }

    /**
     * Get the parent notifiable model (polymorphic).
     * Esto permite que la suscripción pertenezca a un User o a cualquier otro modelo.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
