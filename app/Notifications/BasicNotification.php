<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BasicNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $subject, public $description, public $user_name, public $user_photo, public $url)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        if (app()->environment() == 'production') {
            return ['database', 'mail'];
        } else {
            return ['database'];
        }
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->markdown('emails.basic-email-template', [
                'greeting' => '¡Hola!',
                'intro' => $this->user_name . ' ' . $this->description,
                'url' => $this->url,
                'salutation' => 'Saludos',
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_name' => $this->user_name,
            'user_photo' => $this->user_photo,
            'description' => $this->description,
            'url' => $this->url
        ];
    }
}
