<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeUnionRecipientList extends Model
{
    use HasFactory;

    protected $table = 'trade_union_recipient_lists';

    protected $fillable = [
        'name',
        'description',
        'created_by'
    ];

    public function recipients()
    {
        return $this->hasMany(TradeUnionRecipient::class, 'recipient_list_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
