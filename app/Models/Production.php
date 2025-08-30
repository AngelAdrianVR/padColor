<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'folio',
        'type',
        'client',
        'season',
        'station',
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
        'modified_user_id',
        'start_date',
        'finish_date',
        'close_production_date',
        'quality_released_date',
        'estimated_date',
        'estimated_package_date',
        'quantity',
        'production_close_type',
        'close_quantity',
        'quality_quantity',
        'current_quantity',
        'scrap_quantity',
        'shortage_quantity',

        'production_scrap',
        'production_shortage',
        'quality_scrap',
        'quality_shortage',
        'inspection_scrap',
        'inspection_shortage',

        'returns',
        'look',
        'changes',
        'sheets',

        'close_production_notes',
        'quality_notes',
        'inspection_notes',
        
        'ha',
        'pf',
        'ts',
        'ps',
        'tps',
        'varnish_type',
        'partials',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'materials' => 'array',
        'partials' => 'array',
        'returns' => 'array',
        'estimated_date' => 'date',
        'estimated_package_date' => 'date',
        'start_date' => 'date',
        'finish_date' => 'datetime',
        'close_production_date' => 'datetime',
        'quality_released_date' => 'datetime',
    ];

    //realciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modifiedUser()
    {
        return $this->belongsTo(User::class, 'modified_user_id');
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
