<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeUnionRecipient extends Model
{
    use HasFactory;

    protected $table = 'trade_union_recipients';

    protected $fillable = [
        'recipient_list_id',
        'user_id',
        'channel',
        'price',
        'status'
    ];

    public function recipientList()
    {
        return $this->belongsTo(TradeUnionRecipientList::class, 'recipient_list_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


