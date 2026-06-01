<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operations extends Model
{
    use HasFactory;

    protected $table = 'operations';

    protected $fillable = [
        'quotation_id',
        'data',
        'version',
        'status',
        'created_user_id',
        'updated_user_id',
        'submitted_user_id',
        'approved_user_id',
        'approved_at'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function submittor()
    {
        return $this->belongsTo(User::class, 'submitted_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_user_id');
    }
}
