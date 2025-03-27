<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MemberMail extends Mailable
{
    use Queueable, SerializesModels;
    use Queueable, SerializesModels;
    public $organization;
    public $email;
    public $password;
    public $name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$email, $password, $organization)
    {
        $this->organization = $organization;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject("Welcome to Work Around: " . $this->name. " to " . $this->organization)
                    ->view('emails.email-member-info'); // You need to create this view
    }
}
