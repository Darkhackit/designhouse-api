<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendInvitationToJoinTeam extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $user_exist;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invitation $invitation, bool $user_exist)
    {
        $this->invitation = $invitation;
        $this->user_exist = $user_exist;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->user_exist)
        {

            $url = config('app.client_url').'/settings/teams';
            return $this->markdown('emails.invitaions.invite-existing-user')
                        ->subject('Invitation to join team '.$this->invitation->team->name)
                        ->with(['invitation' => $this->invitation,'url' => $url]);
        }
        else
        {
            $url = config('app.client_url').'/register?invitation='.$this->invitation->recipient_email;
            return $this->markdown('emails.invitaions.invite-new-user')
                        ->subject('Invitation to join team '.$this->invitation->team->name)
                        ->with(['invitation' => $this->invitation,'url' => $url]);
        }

    }
}
