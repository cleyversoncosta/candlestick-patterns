<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $linkTelegram = 'https://t.me/joinchat/GWcy3GOk1a5F_Q7p';

        return (new MailMessage)
            ->subject('Bem vindo ao ' . config('app.name'))
            ->greeting('Olá '.$notifiable->name)
            ->line('Seu cadastro foi realizado com sucesso!')
            ->line('Aproveite para acessar nosso grupo no Telegram e ficar por dentro das últimas novidades.')
            ->action('Acessar no Telegram', $linkTelegram)
            ->line('Qualquer dúvida estamos à disposição.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
