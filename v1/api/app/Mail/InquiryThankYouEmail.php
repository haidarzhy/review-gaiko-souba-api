<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryThankYouEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry, $subject, $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {   
        $this->inquiry = $mailData['inquiry'];
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
        return $this->subject($this->subject)->view('emails.inquiry-thank-you')->with([
            'inquiry' => $this->inquiry,
            'text' => $this->text
        ]);
    }
}
