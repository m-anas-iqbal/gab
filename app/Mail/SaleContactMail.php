<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SaleContactMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $subject;
    public $description;
    public $name;
    public $email;

    public function __construct($subject,$name,$email,$description)
    {
        $this->subject = $subject;
        $this->description = $description;
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                ->view('emails.support-contact-email')  // The view for your email content
                ->with([
                    'name' => $this->name,
                    'description' => $this->description,
                    'email' => $this->email,
                ]);
    }
}
