<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomVoucher extends Model
{
    use HasFactory;

    protected $table = 'custom_vouchers';

    protected $fillable = [
        'uuid',
        'code',
        'expired_from',
        'expired_to',
        'status',
        'respondent_id',
        'sent_at'
    ];

    protected $casts = [
        'expired_from' => 'datetime',
        'expired_to' => 'datetime',
        'sent_at' => 'datetime'
    ];



}
