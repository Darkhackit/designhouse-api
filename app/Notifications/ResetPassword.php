<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as NotificationsResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends NotificationsResetPassword
{


    public function toMail($notifiable)
    {
        $url = url(config('app.client_url').'password/reset/'.$this->token).'?email='.urlencode($notifiable->email);
        return (new MailMessage)
                    ->line('You are receiving this email because you requested a password reset')
                    ->action('Reset Password', $url)
                    ->line('If you did not request for this email then ignore it');
    }


}
