<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechcombankPanel extends Model
{
    use HasFactory;

    protected $table = "techcombank_panel";

    protected $fillable = [
        'id',
        'email',
        'first_name',
        'last_name',
        'phone_number',
        'resource',
        'province_id',
        'gender',
        'year_of_birth',
        'married',
        'householdincome',
        'occupation',
        'education',
        'D1',
        'D2',
        'S9',
        'S10',
        'Q4',
        'AUM',
        'Z1',
        'I1',
        'I2',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
