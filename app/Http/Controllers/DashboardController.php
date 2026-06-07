<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $examsQuery = Exam::where('teacher_id', $user->id);

        $totalExams = (clone $examsQuery)->count();

        $totalQuestions = Question::whereHas('exam', function ($query) use ($user) {
            $query->where('teacher_id', $user->id);
        })->count();

        $totalStudents = Student::where('teacher_id', $user->id)
            ->where('student_code', 'not like', 'TMP%')
            ->count();

        $draftExams = Exam::where('teacher_id', $user->id)
            ->doesntHave('questions')
            ->count();

        $publishedExams = Exam::where('teacher_id', $user->id)
            ->has('questions')
            ->count();

        $stats = [
            'total_exams' => [
                'value' => $totalExams,
                'change' => '',
                'trend' => 'up',
                'icon' => 'file-text'
            ],
            'total_questions' => [
                'value' => $totalQuestions,
                'change' => '',
                'trend' => 'up',
                'icon' => 'help-circle'
            ],
            'total_students' => [
                'value' => $totalStudents,
                'change' => '',
                'trend' => 'up',
                'icon' => 'users'
            ],
            
        ];

        $quickStats = [
            'published_exams' => $publishedExams,
            'draft_exams' => $draftExams,
        ];

        $recentActivity = Exam::where('teacher_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($exam) {
                return [
                    'title' => $exam->title,
                    'action' => 'Examen créé',
                    'time' => $exam->created_at->diffForHumans(),
                    'status' => $exam->questions()->count() > 0 ? 'published' : 'draft'
                ];
            });


        $recentExams = Exam::where('teacher_id', $user->id)
            ->withCount(['questions'])
            ->latest()
            ->take(6)
            ->get()
            ->map(function ($exam) {
            return [
                'id' => $exam->id,
                'title' => $exam->title,
                'course_name' => $exam->course_name,
                'questions_count' => $exam->questions_count,
                'created_at' => $exam->created_at->format('d/m/Y'),
                'scanned_copies' => $exam->resultSessions()->sum('copies_count'),
                'total_points' => $exam->total_points ?? $exam->questions()->sum('points_correct'),
                'status' => $exam->questions_count > 0 ? 'published' : 'draft',
            ];
        });

        return view('dashboard', compact('user', 'stats', 'quickStats', 'recentActivity' , 'recentExams'));
    }
}