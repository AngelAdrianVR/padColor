<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ImportPayment extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $table = 'import_payments';

    protected $fillable = [
        'import_cost_id',
        'amount',
        'payment_date',
        'notes',
    ];

     public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            // Personalizamos la descripción para los pagos
            ->setDescriptionForEvent(fn(string $eventName) => "Un pago relacionado al costo <b>{$this->importCost->concept}</b> (monto: $ {$this->amount}) ha sido {$this->getEventAction($eventName)}");
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

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function importCost()
    {
        return $this->belongsTo(ImportCost::class);
    }
}
