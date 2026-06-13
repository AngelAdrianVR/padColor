<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_key',
        'label',
        'url_path',
        'filename',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
