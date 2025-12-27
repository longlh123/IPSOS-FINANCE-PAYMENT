<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRespondentToken extends Model
{
    use HasFactory;

    protected $table = "project_respondent_tokens";

    protected $fillable = [
        'project_respondent_id',
        'token_public',
        'token_hash',
        'attempts',
        'expires_at',
        'status'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function projectRespondent(){
        return $this->belongsTo(ProjectRespondent::class);
    }
}
