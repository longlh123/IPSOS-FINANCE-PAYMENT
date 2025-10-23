<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectVinnetSMSTransaction extends Model
{
    use HasFactory;

    const STATUS_SMS_SUCCESS = 'Tin nhắn được gửi thành công.';
    const STATUS_SMS_PENDING = 'Tin nhắn đã tạo nhưng chưa gửi đi';
    const STATUS_SMS_SENDING = 'Hệ thống đang gửi tin nhắn';
    const STATUS_SMS_ERROR = 'Hệ thống gửi tin nhắn lỗi.';
    
    protected $table = "project_vinnet_sms_transactions";

    protected $fillable = [
        'vinnet_transaction_id',
        'sms_status'
    ];

    public function vinnetTransaction(){

        return $this->belongsTo(ProjectVinnetTransaction::class, 'vinnet_transaction_id');
    }

    public function updateStatus($status)
    {
        $this->sms_status = $status;
        
        $saved = $this->save();

        return $saved;
    }
}
