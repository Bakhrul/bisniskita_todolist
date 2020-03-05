<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username, $password)
    {
         $this->codeuniq     = $password;
        $this->username     = $username;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->from('noreply@alamraya.club')
                   ->subject("EventZhee - Reset Password Pengguna")
                   ->view('reminder-password')
                   ->with(
                    [
                        'password'      => $this->codeuniq,
                        'username'      => $this->username,
                    ]);    }
}
