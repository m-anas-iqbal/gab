<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchasedMail extends Mailable
{
    use Queueable, SerializesModels;

     /**
     * Create a new message instance.
     *
     * @return void
     */
    public $organization;
    public $user;
    public $plan_name;
    public $amount;
    public $currency;
    public $expairy;
    public $date;
    public $invoice;

    public function __construct($user,$organization,$invoice,$expairy,$date,$plan_name,$currency,$amount)
    {
        $this->organization = $organization;
        $this->user = $user;
        $this->plan_name = $plan_name;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->expairy = $expairy;
        $this->date = $date;
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Thank you for Staying with WorkAround")
                ->view('emails.purchase-plan-mail')  // The view for your email content
                ->with([
                    'organization' => $this->organization,
                    'user' => $this->user,
                    'plan' => $this->plan_name,
                    'amount' => $this->amount,
                    'currency' => $this->currency,
                    'date' => $this->date,
                    'expairy' => $this->expairy,
                    'invoice' => $this->invoice
                ]);
    }
}
