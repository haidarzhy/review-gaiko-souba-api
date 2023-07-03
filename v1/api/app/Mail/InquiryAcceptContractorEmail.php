<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryAcceptContractorEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry, $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {   
        $this->inquiry = $mailData['inquiry'];
        $this->subject = $mailData['subject'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)->view('emails.inquiry-accept-contractor')->with([
            'inquiry' => $this->inquiry,
        ]);
    }
}
