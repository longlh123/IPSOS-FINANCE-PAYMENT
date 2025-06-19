<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'internal_code',
        'project_name',
        'disabled'
    ];

    const STATUS_PLANNED = 'planned'; 
    const STAdUS_IN_COMING = 'in coming'; 
    const STATUS_ON_GOING = 'on going';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ON_HOLD = 'on hold';
    const STATUS_CANCELLED = 'cancelled';
    
    const STATUS_PROJECT_NOT_FOUND = 'Không tìm thấy dự án. Vui lòng liên hệ Admin để biết thêm thông tin.'; // Project not found
    const STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND = 'Dự án đang tạm dừng giao dịch hoặc không tồn tại'; // Project temporarily suspended or does not exist
    const STATUS_PROJECT_NOT_SUITABLE_PRICES = 'Dự án chưa tạo mức giá phù hợp cho mỗi phần quà. Vui lòng liên hệ Admin để biết thêm thông tin.';
    const STATUS_REJECT_REASON_PHONE_NUMBER = 'Từ chối nhập số điện thoại để nhận quà.';

    const ERROR_INTERVIEWER_ID_NOT_REGISTERED = 'Mã số PVV không có trong danh sách đăng ký của dự án này. Vui lòng liên hệ Admin để biết thêm thông tin.';

    public function projectDetails()
    {
        return $this->hasOne(ProjectDetail::class);
    }

    public function projectTypes()
    {
        return $this->belongsToMany(ProjectType::class, 'project_project_types', 'project_id', 'project_type_id');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'project_teams', 'project_id', 'team_id');
    }

    public function projectProvinces()
    {
        return $this->hasMany(ProjectProvince::class);
    }

    public function projectEmployees()
    {
        return $this->hasMany(ProjectEmployee::class);
    }

    public function projectPermissions()
    {
        return $this->hasMany(ProjectPermissions::class);
    }

    public function projectRespondents()
    {
        return $this->hasMany(ProjectRespondent::class);
    }
    
    public function projectVinnetTokens()
    {
        return $this->hasMany(ProjectVinnetToken::class);
    }

    public function projectGotIts()
    {
        return $this->hasMany(ProjectGotIt::class);
    }
}
