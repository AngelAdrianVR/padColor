<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ImportCost extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'import_costs';

    protected $fillable = [
        'import_id',
        'concept',
        'amount',
        'currency',
        'pendent_amount',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            // Personalizamos la descripción para los costos
            ->setDescriptionForEvent(fn(string $eventName) => "El costo <b>{$this->concept}</b> ha sido {$this->getEventAction($eventName)} (monto: $ {$this->amount})");
    }

    private function getEventAction(string $eventName): string
    {
        return match ($eventName) {
            'created' => 'creado',
            'updated' => 'actualizado',
            'deleted' => 'eliminado',
            default => $eventName,
        };
    }

    public function import()
    {
        return $this->belongsTo(Import::class);
    }

    public function payments()
    {
        return $this->hasMany(ImportPayment::class);
    }
}
