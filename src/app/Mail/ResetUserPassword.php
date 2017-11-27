<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetUserPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The token for password reset.
     *
     * @var string
     */
    private $token;

    /**
     * The password reset link url.
     *
     * @var string
     */
    public $reseturl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->reseturl = url('/#/reset_password?token='.$token);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Password reset request')->view('emails.resetuserpassword');
    }
}
