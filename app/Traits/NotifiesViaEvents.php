<?php

namespace App\Traits;

use App\Models\NotificationEvent;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

trait NotifiesViaEvents
{
    /**
     * Envía una notificación a los suscriptores de un evento específico.
     *
     * @param string $eventKey La clave única del evento (ej. 'import.new-status.destination-port')
     * @param mixed $notificationInstance La instancia de la notificación a enviar
     * @return void
     */
    private function sendNotification(string $eventKey, $notificationInstance): void
    {
        $event = NotificationEvent::where('event_key', $eventKey)->first();

        if (!$event) {
            return; // Si el evento no existe, no hacemos nada
        }

        // Obtener usuarios del sistema suscritos
        $userIds = $event->subscriptions()->where('notifiable_type', User::class)->pluck('notifiable_id');
        $users = User::findMany($userIds);

        // Obtener correos externos suscritos
        $externalEmails = $event->subscriptions()->where('notifiable_type', 'external')->pluck('notifiable_id');

        // Enviar notificación a los usuarios del sistema
        if ($users->isNotEmpty()) {
            Notification::send($users, $notificationInstance);
        }

        // Enviar notificación a los correos externos
        foreach ($externalEmails as $email) {
            Notification::route('mail', $email)->notify($notificationInstance);
        }
    }
}
