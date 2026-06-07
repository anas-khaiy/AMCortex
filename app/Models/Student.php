<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
    'first_name',
    'last_name',
    'student_code',
    'teacher_id' ,
];

public function exams()
{
    return $this->belongsToMany(Exam::class); 
}

public function resultSessionStudents()
{
    return $this->hasMany(ResultSessionStudent::class);
}

public function teacher()
{
    return $this->belongsTo(User::class, 'teacher_id');
}
}
