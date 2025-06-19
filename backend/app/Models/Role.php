<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department;
use App\Models\User;

class Role extends Model
{
    use HasFactory;

    //Specify the primary key
    // protected $primaryKey = 'name';
    // public $incrementing = false; //If 'name' is not auto-incrementing
    // protected $keyType = 'string'; // If 'name' is a string

    protected $fillable = [ 'name', 'department_id' ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }
}
