<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProvince extends Model
{
    use HasFactory;

    //Specify the table name
    protected $table = 'project_provinces';

    protected $fillable = [
        'project_id',
        'province_id',
        'sample_size_main',
        'price_main',
        'sample_size_booters',
        'price_boosters'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}