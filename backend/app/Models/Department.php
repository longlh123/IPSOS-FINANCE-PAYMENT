<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use App\Models\Team;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
    ];

    public function roles(){
        return $this->hasMany(Role::class);
    }
    
    public function teams(){
        return $this->hasMany(Team::class);
    }
}
