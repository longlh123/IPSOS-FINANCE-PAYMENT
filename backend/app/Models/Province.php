<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'provinces';

    protected $fillable = [
        'name',
        'code', 
        'abbreviation', 
        'region_id', 
        'area_code'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function gso2025Provinces()
    {
        return $this->belongsToMany(GSO2025Province::class, 'province_mapping', 'province_id', 'gso2025_province_id');
    }

    public function projectProvinces()
    {
        return $this->hasMany(ProjectProvince::class);
    }
}
