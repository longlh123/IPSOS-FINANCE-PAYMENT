<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\User;
use App\Models\Project;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'title'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_teams', 'project_id', 'team_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function parttime_employees()
    {
        return $this->hasMany(ParttimeEmployee::class);
    }
}
