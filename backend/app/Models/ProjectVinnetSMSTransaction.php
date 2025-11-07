<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectVinnetSMSTransaction extends Model
{
    use HasFactory;

    protected $table = "project_vinnet_sms_transactions";

    protected $fillable = [
        'vinnet_transaction_id',
        'sms_status'
    ];

    public function vinnetTransaction(){

        return $this->belongsTo(ProjectVinnetTransaction::class, 'vinnet_transaction_id');
    }

    public function updateStatus($status, $count)
    {
        $this->sms_status = $status;
        $this->sms_count = $count;
        
        $saved = $this->save();

        return $saved;
    }
}
