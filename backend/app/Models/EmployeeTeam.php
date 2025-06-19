<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use App\Models\Team;

class EmployeeTeam extends Model
{
    use HasFactory;

    protected $fillable = [ 'employee_id', 'team_id' ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
