<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
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
        return $this->hasMany(ProjectProvince::class, 'project_id');
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
        return $this->hasMany(ProjectRespondent::class, 'project_id');
    }
    
    public function projectVinnetTokens()
    {
        return $this->hasMany(ProjectVinnetToken::class);
    }

    public function createProjectRespondents(array $data)
    {
        return $this->projectRespondents()->create($data);
    }

    public static function findByInterviewURL($interviewURL): self
    {
        $project = self::with('projectDetails', 'projectRespondents')
            ->where('internal_code', $interviewURL->internal_code)
            ->where('project_name', $interviewURL->project_name)
            ->whereHas('projectDetails', function(Builder $query) use ($interviewURL){
                $query->where('remember_token', $interviewURL->remember_token);
            })->first();

        if(!$project){
            Log::error(self::STATUS_PROJECT_NOT_FOUND);
            throw new \Exception(self::STATUS_PROJECT_NOT_FOUND);
        } else {
            //Log::info('Status of project:' . $project->projectDetails->status);

            if($project->projectDetails->status !== self::STATUS_ON_GOING){
                Log::error(self::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
                throw new \Exception(self::STATUS_PROJECT_SUSPENDED_OR_NOT_FOUND);
            } 
        }

        return $project;
    }

    public function getPriceForProvince($interviewURL)
    {
        $price_item = $this->projectProvinces->firstWhere('province_id', $interviewURL->province_id);

        if(!$price_item){
            Log::error(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES);
            throw new \Exception(Project::STATUS_PROJECT_NOT_SUITABLE_PRICES.' Vui lòng liên hệ Admin để biết thêm thông tin.');
        }

        $price = 0;

        switch($interviewURL->price_level){
            case 'main':
                $price = intval($price_item->price_main);
                break;
            case 'main_1':
                $price = intval($price_item->price_main_1);
                break;
            case 'main_2':
                $price = intval($price_item->price_main_2);
                break;
            case 'main_3':
                $price = intval($price_item->price_main_3);
                break;
            case 'main_4':
                $price = intval($price_item->price_main_4);
                break;
            case 'main_5':
                $price = intval($price_item->price_main_5);
                break;
            case 'booster':
                $price = intval($price_item->price_boosters);
                break;
            case 'booster_1':
                $price = intval($price_item->price_boosters_1);
                break;
            case 'booster_2':
                $price = intval($price_item->price_boosters_2);
                break;
            case 'booster_3':
                $price = intval($price_item->price_boosters_3);
                break;
            case 'booster_4':
                $price = intval($price_item->price_boosters_4);
                break;
            case 'booster_5':
                $price = intval($price_item->price_boosters_5);
                break;
        }

        return $price;
    }

    
}
