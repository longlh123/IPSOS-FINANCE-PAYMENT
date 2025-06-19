<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectType extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'name', 
        'title' 
    ];

    public function projects(){
        return $this->belongsToMany(Project::class, 'project_project_types', 'project_id', 'project_type_id');
    }
}