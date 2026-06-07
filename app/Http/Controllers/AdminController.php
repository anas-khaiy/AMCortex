<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Student;
use App\Models\ResultSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $teachers = User::where('role', 'teacher')->count();
        $admins = User::where('role', 'admin')->count();
        $exams = Exam::count();
        $questions = Question::count();
        $students = Student::where('student_code', 'not like', 'TMP%')->count();
        $sessions = ResultSession::count();

        $latestTeachers = User::where('role', 'teacher')->latest()->take(8)->get();
        $latestExams = Exam::with('teacher')->latest()->take(8)->get();
        $latestStudents = Student::with('teacher')->where('student_code', 'not like', 'TMP%')->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'teachers',
            'admins',
            'exams',
            'questions',
            'students',
            'sessions',
            'latestTeachers',
            'latestExams',
            'latestStudents'
        ));
    }

    public function teachers()
    {
        $teachers = User::where('role', 'teacher')->latest()->paginate(10);
        return view('admin.teachers', compact('teachers'));
    }

    public function createTeacher()
    {
        return view('admin.teacher-create');
    }

    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create([
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
        ]);

        return redirect()->route('admin.teachers')->with('success', 'Enseignant ajouté avec succès.');
    }

    public function editTeacher(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Impossible de modifier un administrateur ici.');
        }

        return view('admin.teacher-edit', compact('user'));
    }

    public function updateTeacher(Request $request, User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Impossible de modifier un administrateur ici.');
        }

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->update([
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('admin.teachers')->with('success', 'Enseignant modifié avec succès.');
    }

    public function deleteTeacher(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Impossible de supprimer un admin.');
        }

        $user->delete();

        return back()->with('success', 'Enseignant supprimé.');
    }

    public function students()
    {
        $students = Student::with('teacher')
            ->where('student_code', 'not like', 'TMP%')
            ->latest()
            ->paginate(15);

        return view('admin.students', compact('students'));
    }

    public function exams()
    {
        $exams = Exam::with('teacher')
            ->withCount('questions')
            ->latest()
            ->paginate(15);

        return view('admin.exams', compact('exams'));
    }

    public function statistics()
{
    $teachers = User::where('role', 'teacher')->count();

    $students = Student::where('student_code', 'not like', 'TMP%')->count();

    $exams = Exam::count();

    $questions = Question::count();

    $sessions = ResultSession::count();

    $examsPerTeacher = User::where('role', 'teacher')
        ->withCount('exams')
        ->get()
        ->map(function ($teacher) {
            return [
                'teacher' => $teacher->full_name,
                'count' => $teacher->exams_count,
            ];
        });

    $studentsPerTeacher = User::where('role', 'teacher')
        ->withCount('students')
        ->get()
        ->map(function ($teacher) {
            return [
                'teacher' => $teacher->full_name,
                'count' => $teacher->students_count,
            ];
        });

    $questionsPerExam = Exam::withCount('questions')
        ->latest()
        ->take(10)
        ->get()
        ->map(function ($exam) {
            return [
                'exam' => $exam->title,
                'count' => $exam->questions_count,
            ];
        });

    $sessionsEvolution = ResultSession::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('date')
        ->orderBy('date')
        ->get();

    return view('admin.statistics', compact(
        'teachers',
        'students',
        'exams',
        'questions',
        'sessions',
        'examsPerTeacher',
        'studentsPerTeacher',
        'questionsPerExam',
        'sessionsEvolution'
    ));
}

}