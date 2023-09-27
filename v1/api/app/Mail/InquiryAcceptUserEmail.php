<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryAcceptUserEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user_name, $user_company_name, $user_address01, $user_address02, $user_url, $subject, $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->user_name = $mailData['name'];
        $this->user_company_name = $mailData['company_name'];
        $this->user_address01 = $mailData['address01'];
        $this->user_address02 = $mailData['address02'];
        $this->user_url = isset($mailData['url']) ? $mailData['url']:'';
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
        return $this->subject($this->subject)->view('emails.inquiry-accept-user')->with([
            'name' => $this->user_name,
            'company_name' => $this->user_company_name,
            'address01' => $this->user_address01,
            'address02' => $this->user_address02,
            'url' => $this->user_url,
            'text' => $this->text
        ]);
    }
}
