<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberGroupStatus extends Mailable
{
    use Queueable, SerializesModels;

    public $organization;
    public $group;
    public $status;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($status, $group, $organization)
    {
        $this->organization = $organization;
        $this->group = $group;
        $this->status = $status;
    }

    public function build()
    {
        return $this->subject("Your request for joinning " . $this->group. " group  has been " . $this->status)
                    ->view('emails.email-member-status'); // You need to create this view
    }
}
