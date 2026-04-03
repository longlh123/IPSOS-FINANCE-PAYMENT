<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\TradeUnionRecipientList;
use App\Models\TradeUnionRecipient;
use Illuminate\Support\Facades\Log;

class GiftNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public TradeUnionRecipientList $recipientList;
    public TradeUnionRecipient $recipient;

    public function __construct(TradeUnionRecipientList $recipientList, TradeUnionRecipient $recipient)
    {
        $this->recipientList = $recipientList;
        $this->recipient = $recipient;
    }

    public function build()
    {
        return $this->from('no-reply@yourdomain.com', config('app.name')) // email thật nhưng hiển thị tên app
                ->subject('Thông báo nhận quà: ' . $this->recipientList->name)
                ->view('emails.gift_notification')
                ->with([
                    'recipientList' => $this->recipientList,
                    'recipient' => $this->recipient,
                ]);
    }
}
