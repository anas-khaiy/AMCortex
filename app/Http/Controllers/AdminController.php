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

    public function teachers(Request $request)
    {
        $search = $request->input('search');

        $teachers = User::where('role', 'teacher')
            ->where('is_approved', true)
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.teachers', compact('teachers', 'search'));
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
        ], [
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà associée à un compte.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        User::create([
            'username' => $validated['username'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'teacher',
            'is_approved' => true,
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
        ], [
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà associée à un compte.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
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

    public function approbations()
    {
        $teachers = User::where('role', 'teacher')
            ->where('is_approved', false)
            ->latest()
            ->paginate(10);
        return view('admin.approbations', compact('teachers'));
    }

    public function approveTeacher(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Action non valide.');
        }

        $user->update(['is_approved' => true]);

        return back()->with('success', 'Enseignant approuvé avec succès.');
    }

    public function students(Request $request)
    {
        $search = $request->input('search');

        $students = Student::with('teacher')
            ->where('student_code', 'not like', 'TMP%')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('student_code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.students', compact('students', 'search'));
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

    public function settings()
    {
        return view('admin.settings');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà associée à un compte.',
        ]);

        auth()->user()->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Mot de passe modifié avec succès.');
    }

}