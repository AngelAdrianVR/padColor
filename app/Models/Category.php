<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'allowed_departments', // NUEVO CAMPO
    ];

    protected $casts = [
        'allowed_departments' => 'array', // CASTEAR A ARREGLO
    ];

    //relationships
    public function ticket() :HasOne
    {
        return $this->hasOne(Ticket::class);
    }
}