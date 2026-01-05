<?php

namespace App\Constants;

class TransactionStatus
{
    const SUCCESS = 'Chúng tôi đã gửi quà thành công. Nếu bạn chưa nhận được tin nhắn, vui lòng liên hệ quản trị viên để được hỗ trợ.';
    const PENDING = 'Tin nhắn đã tạo nhưng chưa gửi đi';
    const SENDING = 'Hệ thống đang gửi tin nhắn';
    const ERROR_C98  = 'Giao dịch chưa xác định kết quả. Vui lòng liên hệ Admin để kiểm tra.';
    const ERROR_C99  = 'Giao dịch thất bại';

    //Token Status
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
    const STATUS_TRANSACTION_FAILED = 'Giao dịch được thực hiện không qua quá trình phỏng vấn.';
    const STATUS_TRANSACTION_TEST = 'Giao dịch test đang được thực hiện';
    const STATUS_INVALID_TRANSACTION = 'Transaction Id is not valid.';

    const ERROR_CODE_CONNECTION_FAILED = 'Could not resolve host';
    const ERROR_SERVICE_NOT_ROUTED     = 'Hệ thống chưa kết nối được đến dịch vụ chuyển quà.';

    public static function all(): array
    {
        return [
            'success' => self::SUCCESS,
            'pending' => self::PENDING,
            'sending' => self::SENDING,
            'error'   => self::ERROR,
        ];
    }
}