<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $name, $plan, $price;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->name = $mailData['name'];
        $this->plan = $mailData['plan'];
        $this->price = $mailData['price'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.register')->with([
                        'name' => $this->name,
                        'plan' => $this->plan,
                        'price' => $this->price,
                    ]);
    }
}
