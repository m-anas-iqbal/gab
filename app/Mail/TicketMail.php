<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $subject;
    public $reason;
    public $file;
    public $user;
    public $priority;

    public function __construct($subject,$priority,$reason,$file,$user)
    {
        $this->subject = $subject;
        $this->priority = $priority;
        $this->reason = $reason;
        $this->file = $file;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                ->view('emails.support-ticket-email')  // The view for your email content
                ->with([
                    'user' => $this->user,
                    'priority' => $this->priority,
                    'reason' => $this->reason,
                    'file' => $this->file,
                ]);
    }
}
