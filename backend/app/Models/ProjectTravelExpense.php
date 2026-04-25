<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTravelExpense extends Model
{
    use HasFactory;

    protected $table = 'travel_expsense';

    protected $fillable = [
        'project_id',
        'employee_id',
        'province_id',
        'departure_date',
        'return_date',
        'working_days',
        'unit_price',
        'vehicle_ticket',
        'unit_price_2',
        'pickup_guests',
        'unit_price_3',
        'total_salary',
        'tax_deduction',
        'remaining_amount',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
