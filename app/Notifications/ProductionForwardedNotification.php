<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Production;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue; // 1. IMPORTAR ShouldQueue

class ProductionForwardedNotification extends Notification implements ShouldQueue // 2. IMPLEMENTAR ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Production $production,
        public readonly string $nextStation,
        public readonly float $unitsPassed // 3. CAMBIAR de 'int' a 'float'
    ) {}

    public function via(object $notifiable): array
    {
        if (app()->environment() == 'production' || true) {
            Log::info("Canal 'mail' seleccionado para " . $notifiable->email . app()->environment()); // Log de depuración
            return ['mail'];
        } else {
            Log::info("Entorno no es 'production', no se envía 'mail'. " . app()->environment()); // Log de depuración
            return [];
        }
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Log::info("Iniciando toMail para producción {$this->production->id}..."); // Log de depuración

        // Stations that require the number of units transferred to be displayed
        $stationsWithUnits = ['X Reproceso', 'Inspección', 'Calidad', 'Empaques'];

        // Special case for 'Material pendiente'
        if ($this->nextStation == 'Material pendiente') {
            Log::info("Notificación enviada para producción {$this->production->id} en 'Material pendiente'.");
            return (new MailMessage)
                ->subject("Avance de Producción: Folio {$this->production->folio}")
                ->greeting("¡Hola!")
                ->line("Tienes una nueva orden pendiente con folio **{$this->production->folio}**, revisar lo antes posible.")
                ->action('Ver Producción', url('/productions/'));
        } 
        // Case for stations that show units
        elseif (in_array($this->nextStation, $stationsWithUnits)) {
            Log::info("Notificación enviada para producción {$this->production->id} en '{$this->nextStation}' con {$this->unitsPassed} unidades.");
            return (new MailMessage)
                ->subject("Avance de Producción: Folio {$this->production->folio}")
                ->greeting("¡Hola!")
                ->line("La producción con folio **{$this->production->folio}** ha sido liberada a la estación **{$this->nextStation}**.")
                ->line("Se han transferido **" . number_format($this->unitsPassed, 2) . "** unidades, revisar lo antes posible.") // Cambiado a 2 decimales
                ->action('Ver Producción', url('/productions/'));
        } 
        // Case for all other stations (without units)
        else {
            Log::info("Notificación enviada para producción {$this->production->id} en '{$this->nextStation}'.");
            return (new MailMessage)
                ->subject("Avance de Producción: Folio {$this->production->folio}")
                ->greeting("¡Hola!")
                ->line("La producción con folio **{$this->production->folio}** ha sido liberada a la estación **{$this->nextStation}**, revisar lo antes posible.")
                ->action('Ver Producción', url('/productions/'));
        }
    }

    public function toArray(object $notifiable): array
    {
        // This is the format that will be saved in the database
        return [
            'title' => "Producción liberada a {$this->nextStation}",
            'message' => "El folio {$this->production->folio} avanzó a la siguiente estación.",
            'data' => [
                'production_id' => $this->production->id,
                'folio' => $this->production->folio,
                'units_passed' => $this->unitsPassed,
            ]
        ];
    }
}
