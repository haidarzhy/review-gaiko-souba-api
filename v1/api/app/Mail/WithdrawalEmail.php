<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WithdrawalEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $user, $reason, $diffEmail, $rEmail, $subject, $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mailData)
    {
        $this->user = $mailData['user'];
        $this->reason = $mailData['reason'];
        $this->diffEmail = $mailData['diff_email'];
        $this->rEmail = $mailData['r_email'];
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
        return $this->subject($this->subject)->view('emails.withdrawal')->with([
            'user' => $this->user,
            'reason' => $this->reason,
            'diffEmail' => $this->diffEmail,
            'rEmail' => $this->rEmail,
            'text' => $this->text
        ]);
    }
}
