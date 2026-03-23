<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomVoucherToken extends Model
{
    use HasFactory;

    protected $table = 'custom_vouchers_token';

    protected $fillable = [
        'employee_id',
        'phone_number',
        'token',
        'attempts',
        'expires_at',
        'batch_id',
        'link',
        'status'
    ];

    public function customVoucher()
    {
        return $this->hasOne(CustomVoucher::class);
    }

    public function logs()
    {
        return $this->hasMany(CustomVoucherTokenLog::class);
    }
}
