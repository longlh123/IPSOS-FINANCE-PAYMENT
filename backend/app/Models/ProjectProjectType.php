<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProjectType extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_type'
    ];

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function project_type(){
        return $this->belongsTo(ProjectType::class);
    }
}
