<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ChangeRequest extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'previous_data' => 'array',
        'decided_at' => 'datetime',
    ];

    /**
     * El producto al que se le quieren aplicar los cambios.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * El usuario que solicitó los cambios.
     */
    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * El usuario que tomó la decisión final.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Los usuarios asignados para revisar esta solicitud.
     */
    public function reviewers()
    {
        return $this->belongsToMany(User::class, 'change_request_user')
                    ->withPivot('status', 'comments')
                    ->withTimestamps();
    }
}
