<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Mail; // Import Mail facade
use App\Mail\GroupInviteMail; // Import your mail class
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGroupInviteEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $inviteLink;
    protected $groupName;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $inviteLink, $groupName)
    {
        $this->email = $email;
        $this->inviteLink = $inviteLink;
        $this->groupName = $groupName;
    }

    public function handle()
    {
        // Send the email
        Mail::to($this->email)->send(new GroupInviteMail($this->inviteLink, $this->groupName));
    }
}
