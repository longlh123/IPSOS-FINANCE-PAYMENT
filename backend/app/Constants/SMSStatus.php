<?php

namespace App\Constants;

class SMSStatus
{
    const SUCCESS = 'Tin nhắn được gửi thành công.';
    const PENDING = 'Tin nhắn đã tạo nhưng chưa gửi đi';
    const SENDING = 'Hệ thống đang gửi tin nhắn';
    const ERROR   = 'Hiện hệ thống đang gặp sự cố. Chúng tôi sẽ kiểm tra và gửi lại quà cho bạn trong vòng 1 giờ.';

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