<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GotitNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $title;
    public string $voucherLink;
    public int $amount;
    public ?string $expiryDate;

    /**
     * Create a new message instance.
     */
    public function __construct(string $title, string $voucherLink, int $amount)
    {
        $this->title = $title;
        $this->voucherLink = $voucherLink;
        $this->amount = $amount;
    }

    public function build()
    {
        return $this->from('no-reply@yourdomain.com', config('app.name'))
                    ->subject('Thông báo nhận quà: ' . $this->title)
                    ->markdown('emails.gotit_notification')
                    ->with([
                        'title' => $this->title,
                        'voucherLink' => $this->voucherLink,
                        'amount' => $this->amount
                    ]);
    }
}
