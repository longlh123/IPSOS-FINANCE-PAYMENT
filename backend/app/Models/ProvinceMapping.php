<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvinceMapping extends Model
{
    use HasFactory;
    
    protected $table = 'province_mapping';

    public function province()
    {
        return $this->belongsTo(Province::class, 'project_id');
    }

    public function gso2025Province()
    {
        return $this->belongsTo(GSO2025Province::class, 'gso2025_province_id');
    }
}
