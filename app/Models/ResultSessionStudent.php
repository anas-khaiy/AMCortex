<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultSessionStudent extends Model
{
    protected $fillable = [
        'result_session_id',
        'student_id',
    ];

    public function session()
    {
        return $this->belongsTo(ResultSession::class, 'result_session_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}