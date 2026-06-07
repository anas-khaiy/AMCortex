<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{

protected $fillable = [

'exam_id',
'question_text',
'question_type',
'points_correct',
'points_penalty',
'shuffle_answers',
'explanation'

];

public function exam()
{
return $this->belongsTo(Exam::class);
}

public function answers()
{
    return $this->hasMany(Answer::class);
}

protected static function booted()
{
    static::deleting(function ($question) {
        $question->answers()->delete();
    });
}

}