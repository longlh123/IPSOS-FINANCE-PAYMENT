<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRespondent extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'respondent_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function respondent()
    {
        return $this->belongsTo(Respondent::class);
    }
}
