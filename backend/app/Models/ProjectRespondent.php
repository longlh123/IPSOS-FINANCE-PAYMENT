<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProjectRespondent extends Model
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

    protected $table = "project_respondents";

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
        'service_code',
        'reject_message',
        'price_level',
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

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function gotitVoucherTransactions()
    {
        return $this->hasMany(ProjectGotItVoucherTransaction::class, 'project_respondent_id');
    }

    public function createGotitVoucherTransactions(array $data)
    {
        return $this->gotitVoucherTransactions()->create($data);
    }

    public function vinnetTransactions()
    {
        return $this->hasMany(ProjectVinnetTransaction::class, 'project_respondent_id');
    }

    public function createVinnetTransactions(array $data)
    {
        return $this->vinnetTransactions()->create($data);
    }

    public static function findByInterviewURL(Project $project, $interviewURL): self
    {
        $projectRespondent = $project->projectRespondents()
                                    ->where('respondent_id', $interviewURL->respondent_id)
                                    ->orWhere('shell_chainid', $interviewURL->shell_chainid)
                                    ->first();

        return $projectRespondent;
    }
    
    public static function checkIfRespondentProcessed(Project $project, $interviewURL)
    {
        $exists = $project->projectRespondents()
                        ->where('respondent_id', $interviewURL->respondent_id)
                        ->exists();
            
        if($exists)
        {
            Log::error(self::ERROR_DUPLICATE_RESPONDENT . ' [Respondent ID: ' . $interviewURL->respondent_id . ']');
            throw new \Exception(self::ERROR_DUPLICATE_RESPONDENT);
        }

        //Kiểm tra shell_chainid của đáp viên đã được thực hiện giao dịch trước đó hay chưa?chưa
        $exists = $project->projectRespondents()
                        ->where('shell_chainid', $interviewURL->shell_chainid)
                        ->exists();
            
        if($exists)
        {
            Log::error(self::ERROR_DUPLICATE_RESPONDENT . ' [Shellchain ID: ' . $interviewURL->shell_chainid . ']');
            throw new \Exception(self::ERROR_DUPLICATE_RESPONDENT);
        }

        //Kiểm tra số điện thoại của đáp viên đã được thực hiện giao dịch trước đó hay chưa?
        $exists = $project->projectRespondents()
                        ->where(function($query) use ($interviewURL) {
                            $query->where('respondent_phone_number', $interviewURL->respondent_phone_number)
                                    ->orWhere('phone_number', $interviewURL->respondent_phone_number);
                })->exists();
        
        if($exists)
        {
            Log::error(self::ERROR_DUPLICATE_RESPONDENT . ' [Respondent Phone number: ' . $interviewURL->respondent_phone_number . ']');
            throw new \Exception(self::ERROR_DUPLICATE_RESPONDENT);
        }               
    }

    public static function checkGiftPhoneNumber(Project $project, $phone_number)
    {
        //Kiểm tra số điện thoại đáp viên nhập đã được nhận quà trước đó chưa?
        $exists = $project->projectRespondents()
                        ->where(function($query) use ($phone_number) {
                            $query->where('respondent_phone_number', $phone_number)
                                ->orWhere('phone_number', $phone_number);
                        })->exists();
        
        if($exists)
        {
            Log::error(self::ERROR_DUPLICATE_RESPONDENT . ' [Phone number: ' . $phone_number . ']');
            throw new \Exception(self::ERROR_DUPLICATE_RESPONDENT);
        } 
    }
    
    public function updateStatus($status): bool
    {
        $oldStatus = $this->status;
        $this->status = $status;
        $saved = $this->save();

        if ($saved) {
            Log::info("Status updated from {$oldStatus} to {$status} for respondent ID {$this->id}");
        }

        return $saved;
    }
    
}
