<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountDeposit extends Model
{
    protected $table = 'account_deposits';

    protected $fillable = [
        'account_type',
        'amount',
    ];

    
}
