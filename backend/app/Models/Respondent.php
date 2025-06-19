<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Respondent extends Model
{
    use HasFactory;
    
    protected $table = 'respondents';

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'address',
        'province_id',
        'phone_number',
        'email',
        'profile_picture'
    ];

    public function province()
    {
        return $this->belognsTo(Province::class);
    }

    public function projectRespondents()
    {
        return $this->hasMany(ProjectRespondent::class);
    }
}
