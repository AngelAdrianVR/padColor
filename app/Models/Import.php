<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Import extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'folio',
        'supplier_id',
        'customs_agent_id',
        'user_id',
        'incoterm',
        'status',
        'estimated_ship_date',
        'estimated_arrival_date',
        'actual_arrival_date',
        'warehouse_delivery_date',
        'notes',
        'currency',
    ];

    protected $casts = [
        'estimated_ship_date' => 'date',
        'estimated_arrival_date' => 'date',
        'actual_arrival_date' => 'date',
        'warehouse_delivery_date' => 'date',
    ];

    // --- INTEGRACIÓN CON SPATIE ACTIVITYLOG ---
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Loguea todos los campos en $fillable
            ->logOnlyDirty() // Solo loguea si hay cambios
            ->setDescriptionForEvent(fn(string $eventName) => "La importación con folio {$this->id} ha sido {$this->getEventAction($eventName)}");
    }

    private function getEventAction(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'creada',
            'updated' => 'actualizada',
            'deleted' => 'eliminada',
            default => $eventName,
        };
    }
    
    // --- RELACIONES ---
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
    
    public function customsAgent(): BelongsTo
    {
        return $this->belongsTo(CustomsAgent::class);
    }

    public function rawMaterials(): BelongsToMany
    {
        return $this->belongsToMany(RawMaterial::class, 'import_raw_material')
            ->withPivot('quantity', 'unit_cost')
            ->withTimestamps();
    }

    public function costs(): HasMany
    {
        return $this->hasMany(ImportCost::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ImportPayment::class);
    }
}