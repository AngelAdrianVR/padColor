<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', // Añadido
        'component_name', // Añadido
        'folio',
        'type',
        'client',
        'season',
        'station',
        'station_times',
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
        
        'packing_close_type',
        'packing_notes',
        'packing_scrap',
        'packing_shortage',
        'packing_partials',
        'packing_received_quantity',
        'packing_received_date',
        'packing_finished_date',
        
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
        'packing_partials' => 'array',
        'station_times' => 'array',
        'estimated_date' => 'date',
        'estimated_package_date' => 'date',
        'start_date' => 'date',
        'finish_date' => 'datetime',
        'close_production_date' => 'datetime',
        'quality_released_date' => 'datetime',
        'packing_received_date' => 'datetime',
        'packing_finished_date' => 'datetime',
    ];

    // --- NUEVAS RELACIONES ---

    /**
     * Obtiene la orden padre (si esta es un componente).
     */
    public function parent()
    {
        return $this->belongsTo(Production::class, 'parent_id');
    }

    /**
     * Obtiene los componentes hijos (si esta es una orden padre).
     */
    public function children()
    {
        return $this->hasMany(Production::class, 'parent_id');
    }

    // --- Relaciones existentes ---
    
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