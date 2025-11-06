<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Production; // Asegúrate de importar tu modelo Production
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProductionReturnedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Production $production,
        public readonly string $returnedToStation,
        public readonly int $unitsReturned,
        public readonly string $reason
    ) {}

    public function via(object $notifiable): array
    {
        if (app()->environment() == 'production') {
            Log::info("Canal 'mail' seleccionado para " . $notifiable->email . app()->environment()); // Log de depuración
            return ['mail'];
        } else {
            Log::info("Entorno no es 'production', no se envía 'mail'. " . app()->environment()); // Log de depuración
            return [];
        }
    }

    public function toMail(object $notifiable): MailMessage
    {
        Log::info("Notificación enviada para producción {$this->production->folio} en '{$this->returnedToStation}'.");
        return (new MailMessage)
            ->subject("Regreso de Producción: Folio {$this->production->folio}")
            ->greeting("¡Hola!")
            ->line("La producción con folio **{$this->production->folio}** ha sido regresada a la estación **{$this->returnedToStation}**.")
            ->line("Motivo: *{$this->reason}*")
            ->line("Unidades regresadas: **{$this->unitsReturned}**.")
            ->action('Ver Producción', url('/productions/'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Producción regresada a {$this->returnedToStation}",
            'message' => "El folio {$this->production->folio} fue regresado por un problema.",
            'data' => [
                'production_id' => $this->production->id,
                'folio' => $this->production->folio,
                'units_returned' => $this->unitsReturned,
                'reason' => $this->reason,
            ]
        ];
    }
}
