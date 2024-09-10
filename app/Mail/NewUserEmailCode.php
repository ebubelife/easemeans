<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserEmailCode extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $name;
    public $verificationLink;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $otp, $verificationLink)
    {
        $this->name = $name;
        $this->otp = $otp;
        $this->verificationLink = $verificationLink;
    }

    public function build()
    {
      

        $message = 'This is an example email sent from Laravel.';

        return $this->view('emails/new_user_otp', ['message' => $message])
                    ->with(['otp'=> $this->otp, 'firstName'=>$this->name, 'verificationLink'=>$this->verificationLink])
                   
                    ->from('Easmeans@easmeans.com')
                    ->subject('One more step!');
    }


   
}
