<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendCodeEmailCheck extends Mailable
{
    use Queueable, SerializesModels;

    public $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('OTP For Email Verification')
                ->view('emails.send-code-email-verification')  // The view for your email content
                ->with([
                    'code' => $this->code,  // Passing the data to the view
                ]);
        // return $this->markdown('emails.send-code-email-verification');
    }
}
