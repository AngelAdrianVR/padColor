<?php

namespace App\Notifications;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportArrivedAtDestination extends Notification implements ShouldQueue
{
    use Queueable;

    public $import;

    public function __construct(Import $import)
    {
        // Nos aseguramos de que la relación esté cargada para usarla en la vista
        $this->import = $import->load('supplier', 'rawMaterials');
    }

    public function via(object $notifiable): array
    {
        if (app()->environment() == 'production') {
            return ['mail'];
        } else {
            return [];
        }
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Llegada de Importación a Puerto: Folio ' . $this->import->id)
            ->markdown('emails.imports.arrived', ['import' => $this->import]);
    }
}
