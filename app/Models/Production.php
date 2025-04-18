<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'client',
        'season',
        'station',
        'quantity',
        'materials',
        'material',
        'width',
        'gauge',
        'large',
        'dfi',
        'pps',
        'adjust',
        'faces',
        'notes',
        'product_id',
        'machine_id',
        'user_id',
        'start_date',
        'estimated_date',
        'current_quantity',
        'look',
        'changes',
        'sheets',
        'ha',
        'pf',
        'ts',
        'ps',
        'tps',
    ];

    protected $casts = [
        'materials' => 'array',
        'estimated_date' => 'date',
        'start_date' => 'date',
    ];

    //realciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
