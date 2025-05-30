<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'code',
        'description',
        'season',
        'branch',
        'measure_unit',
        'width',
        'large',
        'height',
        'material',
        'stock',
        'min_stock',
        'max_stock',
        'price',
        'created_at'
    ];
}
