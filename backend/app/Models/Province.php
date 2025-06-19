<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'abbreviation', 
        'region_id', 
        'area_code'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function projectProvinces()
    {
        return $this->hasMany(ProjectProvince::class);
    }
}
