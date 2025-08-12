<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectGotItSmsTransaction extends Model
{
    use HasFactory;

    const STATUS_SMS_SUCCESS = 'Tin nhắn được gửi thành công.';
    const STATUS_SMS_PENDING = 'Tin nhắn đã tạo nhưng chưa gửi đi';
    const STATUS_SMS_SENDING = 'Hệ thống đang gửi tin nhắn';
    const STATUS_SMS_ERROR = 'Hệ thống gửi tin nhắn lỗi.';

    protected $table = 'project_gotit_sms_transactions';

    protected $fillable = [
        'voucher_transaction_id',
        'transaction_ref_id',
        'sms_status',
    ];

    public function gotitVoucherTransaction()
    {
        return $this->belongsTo(ProjectGotItVoucherTransaction::class, 'voucher_transaction_id');
    }

    public function updateStatus($status)
    {
        $this->sms_status = $status;
        
        $saved = $this->save();

        return $saved;
    }
}
