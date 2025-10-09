<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectIUURespondent extends Model
{
    use HasFactory;

    const STATUS_RESPONDENT_PENDING =           'Đang chờ xử lý kết quả khảo sát / đợi xác nhận điều kiện nhận quà.';
    const STATUS_RESPONDENT_QUALIFIED =         'Đủ điều kiện nhận quà.';
    const STATUS_RESPONDENT_WAITING_FOR_GIFT =  'Đang đợi được phát quà.';
    const STATUS_RESPONDENT_GIFT_DISPATCHED =   'Quà đã được gửi đi (giao hàng / phát tại điểm khảo sát).';
    const STATUS_RESPONDENT_GIFT_RECEIVED =     'Đã nhận quà.';
    const STATUS_RESPONDENT_DISQUALIFIED =      'Không đủ điều kiện nhận quà.';
    const STATUS_RESPONDENT_DUPLICATE =         'Trùng thông tin / khảo sát đã được thực hiện trước đó.';     
    const STATUS_RESPONDENT_CANCELLED =         'Khảo sát bị hủy / không hoàn thành.'; 
    const STATUS_RESPONDENT_REJECTED =          'Đáp viên từ chối nhận quà.';    

    const ERROR_CANNOT_STORE_RESPONDENT =                 'Đáp viên không thể lưu.';
    const ERROR_INVALID_RESPONDENT_STATUS_FOR_UPDATE =    'Đáp viên không hợp lệ để cập nhật trạng thái.';
    const ERROR_DUPLICATE_RESPONDENT =                    'Đáp viên đã tồn tại.';

    protected $table = "project_iuu_respondents";

    protected $fillable = [
        'project_id',
        'employee_id',
        'province_id',
        'first_name',
        'last_name',
        'phone_number',
        'channel',
        'gift_denomination',
        'send_count',
        'quota_description',
        'service_code',
        'channel',
        'status',
    ];

    public const STATUSES = [
        self::STATUS_RESPONDENT_PENDING,
        self::STATUS_RESPONDENT_QUALIFIED,
        self::STATUS_RESPONDENT_WAITING_FOR_GIFT,
        self::STATUS_RESPONDENT_GIFT_DISPATCHED,
        self::STATUS_RESPONDENT_GIFT_RECEIVED,
        self::STATUS_RESPONDENT_DISQUALIFIED,
        self::STATUS_RESPONDENT_DUPLICATE,
        self::STATUS_RESPONDENT_CANCELLED,
        self::STATUS_RESPONDENT_REJECTED,
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

}
