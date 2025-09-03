<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GSO2025District extends Model
{
    use HasFactory;

    protected $table = 'gso2025_districts';

    protected $fillable = [
        'name',
        'code',
        'land_area',
        'population'
    ];

    public function gso2025Province()
    {
        return $this->belongsTo(GSO2025Province::class);
    }
}
