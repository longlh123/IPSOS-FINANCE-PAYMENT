<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomVoucherTokenLog extends Model
{
    use HasFactory;

    protected $table = 'custom_vouchers_token_logs';

    protected $fillable = [
        'token_id'
    ];

    public function customVoucherToken()
    {
        return $this->belongsTo(CustomVoucherToken::class);
    }
}
