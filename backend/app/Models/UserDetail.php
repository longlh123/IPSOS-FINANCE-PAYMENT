<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'address',
        'phone_number',
        'profile_picture',
        'role_id',
        'department_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function hasAnyRole($roles)
    {
        if(is_array($roles)){
            return $this->role()->whereIn('name', (array) $roles)->exists();
        }
        else {
            return $this->role()->where('name', $roles)->exists();
        }
    }

    public function projectPermissions()
    {
        return $this->hasMany(ProjectPermissions::class);
    }
}
