<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechcombankSurveys extends Model
{
    use HasFactory;

    protected $table = "techcombank_surveys";

    protected $fillable = [
        'id',
        'name',
        'engagment',
        'project_type',
        'sent_out',
        'respond',
        'respond_rate',
        'completed_qualified',
        'cancellation',
        'number_of_question',
        'open_date',
        'close_date',
        'resource'
    ];
}
