<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Confirm extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($action, $url_root, $email, $token)
    {
        $this->action   = $action;
        $this->url_root = $url_root;
        $this->email    = $email;
        $this->token    = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('noreply@ssh.pub')
            ->view('emails.confirm')->with([
                'action' => $this->action,
                'url_root' => $this->url_root,
                'email' => $this->email,
                'token' => $this->token
            ]);
    }
}
