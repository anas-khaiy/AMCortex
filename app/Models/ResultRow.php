<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultRow extends Model
{
    protected $fillable = [
        'result_session_id',
        'copie',
        'code',
        'nom',
        'note',
    ];

    public function session()
    {
        return $this->belongsTo(ResultSession::class, 'result_session_id');
    }
}