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
        'recipient_type',
        'vinnet_invoice_date'
    ];

    const SUCCESS_TRANSACTION_CONTACT_ADMIN_IF_NO_GIFT = 'Chúng tôi đã gửi thành công. Vui lòng kiểm tra lại tin nhắn của bạn.';
    const STATUS_UNDETERMINED_TRANSACTION_RESULT = 'Giao dịch chưa xác định kết quả. Vui lòng liên hệ Admin để kiểm tra. Hoặc gọi api checktransaction để truy vấn kết quả'; // Undetermined transaction result. Please contact Admin for verification or call the checktransaction API to query the result 
    
    const STATUS_NOT_RECEIVED = 'Không nhận được Token từ client';
    const STATUS_NOT_VERIFIED = 'Không xác thực được Token';
    const STATUS_EXPIRED = 'Token hết hạn';
    const STATUS_ISSUED = 'Token đã được phát hành'; 
    const STATUS_ACTIVE = 'Token đang hoạt động'; 
    const STATUS_USED = 'Token đã được sử dụng';
    const STATUS_REVOKED = 'Token đã bị thu hồi';
    const STATUS_INVALID = 'Token không hợp lệ'; 
    const STATUS_PENDING_VERIFICATION = 'Token đang chờ xác thực'; 
    const STATUS_VERIFIED = 'Token đã được xác thực';
    const STATUS_SUSPENDED = 'Token đã bị đình chỉ';
    const STATUS_RENEWAL_PENDING = 'Token đang chờ gia hạn';
    const STATUS_RENEWAL_COMPLETED = 'Token đã gia hạn xong'; 
    const STATUS_ERROR = 'Lỗi Token'; 

    const STATUS_INVALID_DENOMINATION = 'Mệnh giá quà không hợp lệ'; // Invalid denomination
    const STATUS_TRANSACTION_FAILED = 'Giao dịch được thực hiện không qua quá trình phỏng vấn. Vui lòng liên hệ Admin để kiểm tra.';

    public function respondent()
    {
        return $this->belongsTo(ProjectRespondent::class, 'project_respondent_id');
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
        }
        
        $this->vinnet_token_status = $status;
        $this->vinnet_token_message = $message;

        $saved = $this->save();

        return $saved;
    }
}
