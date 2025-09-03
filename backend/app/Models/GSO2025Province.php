<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GSO2025Province extends Model
{
    use HasFactory;

    protected $table = 'gso2025_provinces';

    protected $fillable = [
        'name',
        'code',
        'land_area',
        'population'
    ];

    public function provinces()
    {
        return $this->belongsToMany(Province::class, 'province_mapping', 'gso2025_province_id', 'province_id');
    }

    public function districts()
    {
        return $this->hasMany(GSO2025District::class, 'gso2025_province_id');
    }
}
