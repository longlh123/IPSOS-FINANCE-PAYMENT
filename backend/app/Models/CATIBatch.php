<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CATIBatch extends Model
{
    use HasFactory;

    protected $table = "cati_batches";

    protected $fillable = [
        'project_id',
        'name',
        'status',
        'total_records',
        'created_user_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function respondents()
    {
        return $this->hasMany(CATIRespondent::class, 'batch_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }
}
