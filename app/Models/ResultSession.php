<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultSession extends Model
{
    protected $fillable = [
        'exam_id',
        'mode',
        'label',
        'csv_path',
        'copies_count',
        'corrected_zip_path',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function rows()
    {
        return $this->hasMany(ResultRow::class);
    }

    public function sessionStudents()
    {
        return $this->hasMany(ResultSessionStudent::class);
    }
}