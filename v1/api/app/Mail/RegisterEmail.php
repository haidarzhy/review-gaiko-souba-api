<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $name, $password, $plan, $price, $subject, $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->name = $mailData['name'];
        $this->password = $mailData['password'];
        $this->plan = $mailData['plan'];
        $this->price = $mailData['price'];
        $this->subject = $mailData['subject'];
        $this->text = $mailData['text'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('emails.register')->with([
                        'name' => $this->name,
                        'password' => $this->password,
                        'plan' => $this->plan,
                        'price' => $this->price,
                        'text' => $this->text
                    ]);
    }
}
