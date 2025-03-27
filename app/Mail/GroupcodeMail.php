<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GroupcodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $code;
    public $groupName;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($code, $groupName)
    {
        $this->code = $code;
        $this->groupName = $groupName;
    }

    public function build()
    {
        return $this->subject("Code For join Work Around group: " . $this->groupName)
                    ->view('emails.email-join-code'); // You need to create this view
    }
}
