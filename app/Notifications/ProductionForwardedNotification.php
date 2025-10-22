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
        if (app()->environment() == 'local') {
            return ['mail'];
        } else {
            return [];
        }
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Stations that require the number of units transferred to be displayed
        $stationsWithUnits = ['X Reproceso', 'Inspección', 'Calidad', 'Empaques'];

        // Special case for 'Material pendiente'
        if ($this->nextStation == 'Material pendiente') {
            return (new MailMessage)
                ->subject("Avance de Producción: Folio {$this->production->folio}")
                ->greeting("¡Hola!")
                ->line("Tienes una nueva orden pendiente con folio **{$this->production->folio}**, revisar lo antes posible.")
                ->action('Ver Producción', url('/productions/'));
        } 
        // Case for stations that show units
        elseif (in_array($this->nextStation, $stationsWithUnits)) {
            return (new MailMessage)
                ->subject("Avance de Producción: Folio {$this->production->folio}")
                ->greeting("¡Hola!")
                ->line("La producción con folio **{$this->production->folio}** ha sido liberada a la estación **{$this->nextStation}**.")
                ->line("Se han transferido **" . number_format($this->unitsPassed, 1) . "** unidades, revisar lo antes posible.")
                ->action('Ver Producción', url('/productions/'));
        } 
        // Case for all other stations (without units)
        else {
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