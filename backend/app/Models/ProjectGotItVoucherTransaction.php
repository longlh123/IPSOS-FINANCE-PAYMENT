<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectGotItVoucherTransaction extends Model
{
    use HasFactory;

    const STATUS_PENDING_VERIFICATION = 'Giao dịch đang chờ xác thực.';
    const STATUS_VOUCHER_SUCCESS = 'Voucher được cập nhật thành công.';
    

    protected $table = 'project_gotit_voucher_transactions';

    protected $fillable = [
        'project_respondent_id',
        'transaction_ref_id',
        'expiry_date',
        'order_name',
        'amount',
        'voucher_link',
        'voucher_link_code',
        'voucher_image_link',
        'voucher_cover_link',
        'voucher_serial',
        'voucher_expired_date',
        'voucher_product_id',
        'voucher_price_id',
        'voucher_value',
        'voucher_status',
        'invoice_date'
    ];

    public function respondent()
    {
        return $this->belongsTo(ProjectRespondent::class, 'project_respondent_id');
    }

    public function gotitSMSTransaction()
    {
        return $this->hasOne(ProjectGotItSMSTransaction::class, 'voucher_transaction_id');
    }

    public function createGotitSMSTransaction(array $data)
    {
        return $this->gotitSmsTransaction()->create($data);
    }

    public function updateGotitVoucherTransaction($voucherData): bool
    {
        $this->voucher_link = $voucherData['voucher_link'];

        if(strlen($voucherData['voucher_cover_link']) > 0)
        {
            $this->voucher_cover_link = $voucherData['voucher_cover_link'];
        }

        $this->voucher_serial = $voucherData['voucher_serial'];
        $this->voucher_value = $voucherData['value'];
        $this->voucher_expired_date = $voucherData['expired_date'];
        $this->voucher_status = self::STATUS_VOUCHER_SUCCESS;
        
        $saved = $this->save();

        return $saved;
    }
}
