<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectVinnetToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'shell_chainid',
        'respondent_id',
        'employee_id',
        'province_id',
        'interview_start',
        'interview_end',
        'respondent_phone_number',
        'phone_number',
        'vinnet_token',
        'vinnet_token_requuid',
        'vinnet_serviceitems_requuid',
        'vinnet_payservice_requuid',
        'vinnet_token_order',
        'vinnet_token_status',
        'status',
        'total_amt',
        'commission',
        'discount',
        'payment_amt',
        'vinnet_invoice_date',
        'reject_message'
    ];

    const ERROR_CODE_CONNECTION_FAILED = 'Không thể kết nối đến hệ thống. Vui lòng kiểm tra kết nối mạng thiết bị của bạn.';
    const ERROR_TRANSACTION_DENIED_FEEDBACK_RECORDED = 'Bạn đã từ chối dịch vụ chuyển quà qua tin nhắn và chúng tôi đã ghi nhận thông tin đóng góp ý kiến của bạn.';
    
    const SUCCESS_TRANSACTION_CONTACT_ADMIN_IF_NO_GIFT = 'Chúng tôi đã gửi thành công. Vui lòng kiểm tra lại tin nhắn của bạn.';

    const STATUS_SUCCESS = 'Thành công'; // Success
    const STATUS_TRANSACTION_ALREADY_EXISTS = 'Mã giao dịch đã tồn tại. Vui lòng liên hệ Admin để biết thêm thông tin.'; // Transaction already exists

    
    const STATUS_TOKEN_NOT_RECEIVED = 'Không nhận được Token từ client'; // Token not received from client 
    const STATUS_TOKEN_NOT_VERIFIED = 'Không xác thực được Token'; // Token could not be verified
    const STATUS_TOKEN_EXPIRED = 'Token hết hạn'; // Token expired
    const STATUS_NO_REQUEST_RECEIVED = 'Không nhận được request'; // No request received
    const STATUS_NO_DATA_RECEIVED = 'Không nhận được data request'; // No data received in request
    const STATUS_NO_PUBLIC_KEY_FOUND = 'Không tìm thấy public key của merchant'; // No public key found for merchant
    const STATUS_INVALID_SIGNATURE = 'Chữ ký không hợp lệ'; // Invalid signature
    const STATUS_INVALID_REQUEST_ID = 'Mã request không đúng định dạng UUID'; // Invalid request ID format (UUID)
    const STATUS_NO_MERCHANT_ACCOUNT_FOUND = 'Không tìm thấy tài khoản merchant'; // No merchant account found
    const STATUS_MERCHANT_ACCOUNT_LOCKED = 'Tài khoản merchant đang tạm khóa'; // Merchant account is temporarily locked
    const STATUS_INCORRECT_MERCHANT_KEY = 'Merchant_key không đúng'; // Incorrect merchant_key
    const STATUS_CHANGE_MERCHANT_KEY = 'Vui lòng thay đổi merchant_key'; // Please change the merchant_key
    const STATUS_PRODUCT_NOT_FOUND = 'Không tìm thấy sản phẩm'; // Product not found
    const STATUS_PRODUCT_SUSPENDED = 'Sản phẩm đang tạm dừng giao dịch'; // Product temporarily suspended
    const STATUS_SERVICE_SUSPENDED_OR_NOT_FOUND = 'Dịch vụ đang tạm dừng giao dịch hoặc không tồn tại'; // Service temporarily suspended or does not exist
    const STATUS_NO_DISCOUNT_SET_FOR_SERVICE = 'Chưa set chiết khấu dịch vụ cho merchant'; // No discount set for service for merchant
    const STATUS_SERVICE_NOT_ROUTED = 'Dịch vụ chưa được định tuyến'; // Service not routed
    const STATUS_INSUFFICIENT_ACCOUNT_BALANCE = 'Số dư tài khoản không đủ giao dịch'; // Insufficient account balance for transaction
    const STATUS_ORIGINAL_TRANSACTION_NOT_FOUND = 'Không tìm thấy giao dịch gốc'; // Original transaction not found
    const STATUS_INVALID_QUANTITY_PARAMETER = 'Giá trị tham số quantity không hợp lệ với dịch vụ thanh toán'; // Invalid quantity parameter for payment service
    const STATUS_INSUFFICIENT_CARD_QUANTITY = 'Không đủ số lượng thẻ'; // Insufficient card quantity
    const STATUS_INVALID_DENOMINATION = 'Mệnh giá không hợp lệ'; // Invalid denomination
    const STATUS_TRANSACTION_FAILED = 'Giao dịch thất bại. Tùy trường hợp sẽ có những mô tả chi tiết khác nhau'; // Transaction failed. Detailed descriptions may vary
    const STATUS_UNDETERMINED_TRANSACTION_RESULT = 'Giao dịch chưa xác định kết quả. Vui lòng liên hệ Admin để kiểm tra. Hoặc gọi api checktransaction để truy vấn kết quả'; // Undetermined transaction result. Please contact Admin for verification or call the checktransaction API to query the result 
    
    const STATUS_NOT_RECEIVED = 'Token not received from client'; // Không nhận được Token từ client
    const STATUS_NOT_VERIFIED = 'Token could not be verified'; // Không xác thực được Token
    const STATUS_EXPIRED = 'Token expired'; // Token hết hạn
    const STATUS_ISSUED = 'Token issued'; // Token đã được phát hành
    const STATUS_ACTIVE = 'Token active'; // Token đang hoạt động
    const STATUS_USED = 'Token used'; // Token đã được sử dụng
    const STATUS_REVOKED = 'Token revoked'; // Token đã bị thu hồi
    const STATUS_INVALID = 'Token invalid'; // Token không hợp lệ
    const STATUS_PENDING_VERIFICATION = 'Token pending verification'; // Token đang chờ xác thực
    const STATUS_VERIFIED = 'Token verified'; // Token đã được xác thực
    const STATUS_SUSPENDED = 'Token suspended'; // Token đã bị đình chỉ
    const STATUS_RENEWAL_PENDING = 'Token renewal pending'; // Token đang chờ gia hạn
    const STATUS_RENEWAL_COMPLETED = 'Token renewal completed'; // Token đã gia hạn xong
    const STATUS_ERROR = 'Token error'; // Lỗi Token

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
