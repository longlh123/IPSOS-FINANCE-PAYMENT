<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectGotIt extends Model
{
    use HasFactory;

    protected $table = 'project_gotit_transactions';

    protected $fillable = [
        'id',
        'project_id',
        'shell_chainid',
        'respondent_id',
        'employee_id',
        'province_id',
        'interview_start',
        'interview_end',
        'respondent_phone_number',
        'phone_number',
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
        'sms_status',
        'invoice_date',
        'status',
        'reject_message',
    ];

    const MIN_VOUCHER_E_VALUE = 50000;

    const STATUS_SUCCESS = 'Giao dịch thành công.'; // Success
    
    const STATUS_VOUCHER_SUCCESS = 'Voucher được cập nhật thành công.';
    const STATUS_SEND_SMS_SUCCESS = 'SMS voucher gửi cho đáp viên thành công.';
    const STATUS_SEND_SMS_FAILED = 'SMS voucher gửi thất bại. Vui lòng liên hệ Admin để biết thêm thông tin.';

    const STATUS_PENDING_VERIFICATION = 'Giao dịch đang chờ xác thực.';
    const STATUS_TRANSACTION_ALREADY_EXISTS = 'Giao dịch đã tồn tại. Vui lòng liên hệ Admin để biết thêm thông tin.'; // Transaction already exists
    const STATUS_TRANSACTION_NOT_EXISTS = 'Giao dịch không tồn tại.';
    const STATUS_NO_PERMISSION = 'Account does not has permission.';
    const STATUS_MIN_VOUCHER_E_VALUE = 'Voucher value must be greater than ' . self::MIN_VOUCHER_E_VALUE . '.';
    const STATUS_ERROR = 'Giao dịch thất bại.';

    const ERROR_TRANSACTION_DENIED_FEEDBACK_RECORDED = 'Bạn đã từ chối dịch vụ chuyển quà qua tin nhắn và chúng tôi đã ghi nhận thông tin đóng góp ý kiến của bạn.';

    const STATUS_PRODUCT_NOT_ALLOWED = 'Product ID is not allowed';

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function province(){
        return $this->belongsTo(Province::class);
    }
}
