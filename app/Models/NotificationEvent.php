<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_key',
        'name',
        'description',
    ];

    public function subscriptions()
    {
        return $this->hasMany(NotificationSubscription::class);
    }
}
