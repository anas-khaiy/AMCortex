<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanAssociation extends Model
{
    protected $fillable = [
        'exam_id',
        'scan_file',
        'student_id',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}