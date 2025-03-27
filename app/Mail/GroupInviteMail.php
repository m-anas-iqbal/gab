<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GroupInviteMail extends Mailable
{
    use Queueable, SerializesModels;
    public $inviteLink;
    public $groupName;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inviteLink, $groupName)
    {
        $this->inviteLink = $inviteLink;
        $this->groupName = $groupName;
    }

    public function build()
    {
        return $this->subject("Invitation to join Work Around group: " . $this->groupName)
                    ->view('emails.email-invitation-join'); // You need to create this view
    }
}
