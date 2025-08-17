<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Production;

class ProductionForwardedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Production $production,
        public readonly string $nextStation,
        public readonly int $unitsPassed
    ) {}

    public function via(object $notifiable): array
    {
        if (app()->environment() == 'production') {
            return ['mail'];
        } else {
            return [];
        }
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Avance de Producción: Folio {$this->production->folio}")
            ->greeting("¡Hola!")
            ->line("La producción con folio **{$this->production->folio}** ha sido liberada a la estación **{$this->nextStation}**.")
            ->line("Se han transferido **" . number_format($this->unitsPassed, 1) . "** unidades.")
            ->action('Ver Producción', url('/productions/'));
    }

    public function toArray(object $notifiable): array
    {
        // Este es el formato que se guardará en la base de datos
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
