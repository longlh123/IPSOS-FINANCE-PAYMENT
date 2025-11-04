<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProjectVinnetTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_respondent_id',
        'vinnet_token_requuid',
        'vinnet_serviceitems_requuid',
        'vinnet_payservice_requuid',
        'vinnet_token',
        'vinnet_token_order',
        'vinnet_token_status',
        'vinnet_token_message',
        'total_amt',
        'commission',
        'discount',
        'payment_amt',
        'card_serial_no',
        'card_pin_code',
        'card_expiry_date',
        'recipient_type',
        'vinnet_invoice_date'
    ];

    public function respondent()
    {
        return $this->belongsTo(ProjectRespondent::class, 'project_respondent_id');
    }

    public function vinnetSMSTransaction()
    {
        return $this->hasOne(ProjectVinnetSMSTransaction::class, 'vinnet_transaction_id');
    }

    public function createVinnetSMSTransaction(array $data)
    {
        return $this->vinnetSMSTransaction()->create($data);
    }

    public function updatePaymentServiceStatus($requuid, $pay_item, $status, $message): bool
    {
        $this->vinnet_payservice_requuid = $requuid;

        if(!empty($pay_item))
        {
            $this->total_amt = $pay_item['totalAmt'];
            $this->commission = $pay_item['commission'];
            $this->discount = $pay_item['discount'];
            $this->payment_amt = $pay_item['paymentAmt'];
            $this->recipient_type = $pay_item['recipientType'];

            if(!empty($pay_item['cardItems']) && is_array($pay_item['cardItems'])){
                $card_item = $pay_item['cardItems'][0];

                $this->card_serial_no = $card_item['serialNo'] ?? null;
                $this->card_pin_code = $card_item['pinCode'] ?? null;
                $this->card_expiry_date = $card_item['expiryDate'] ?? null;
            }
        }
        
        $this->vinnet_token_status = $status;
        $this->vinnet_token_message = $message;

        $saved = $this->save();

        return $saved;
    }
}
