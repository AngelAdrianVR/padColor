<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketAssignmentRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'department',
        'allowed_departments',
    ];

    protected $casts = [
        'allowed_departments' => 'array',
    ];
}