<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [

        // Informations générales
        'title',
        'description',
        'course_name',
        'teacher_name',



        // Paramètres examen
        'duration',
        'total_points',
        'exam_date',

        'exam_language',

        // Paramètres feuille
        'page_format',

        // Paramètres AMC
        'copies_number',
        'student_id_length',
        'instructions',

        // Randomisation
        'shuffle_questions',
        'shuffle_answers',

        // Scan AMC
        'scan_status',
        'last_amc_log',
        'last_amc_error',

        // Verrouillage de l'examen
        'is_locked',

        // Relation
        'teacher_id',

        
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    // Un examen appartient à un enseignant
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Un examen possède plusieurs questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Permet de faire $exam->total_calculated_points
    public function getTotalCalculatedPointsAttribute()
   {
        return $this->questions()->sum('points_correct');
   }

   public function scans()
   {
        return $this->hasMany(ScannedCopy::class);
   }

   public function scanAssociations()
   {
        return $this->hasMany(ScanAssociation::class);
   }

   public function resultSessions()
   {
        return $this->hasMany(ResultSession::class);
   }


   protected static function booted()
    {
        static::deleting(function ($exam) {
            $exam->questions()->delete();
            $exam->scanAssociations()->delete();
            $exam->resultSessions()->delete();
        });
    }

}