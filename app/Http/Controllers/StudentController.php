<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\Exam;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $students = Student::query()
            ->where('teacher_id', auth()->id())
            ->where('student_code', 'not like', 'TMP%')
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('student_code', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        $activeCount = Student::where('teacher_id', auth()->id())
            ->where('student_code', 'not like', 'TMP%')
            ->where('updated_at', '>=', now()->subMonth())
            ->count();

        $newCount = Student::where('teacher_id', auth()->id())
            ->where('student_code', 'not like', 'TMP%')
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        return view('students.index', compact('students', 'activeCount', 'newCount'));
    }

    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:csv,txt'
    ]);

    $file = $request->file('file');
    $handle = fopen($file->getRealPath(), 'r');
    
    if (!$handle) {
        return back()->with('error', 'Impossible de lire le fichier CSV.');
    }

    $header = fgetcsv($handle, 1000, ";"); 

    $importCount = 0;
    $errors = 0;
    $errorMessages = [];
    $seenCodes = [];

    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if (isset($data[0], $data[1], $data[2])) {
            $code = trim($data[0]);
            $firstName = trim($data[1]);
            $lastName = trim($data[2]);

            if ($code === '' || $firstName === '' || $lastName === '') {
                $errors++;
                $errorMessages[] = "Code, prénom et nom sont obligatoires.";
                continue;
            }

            if (!ctype_digit($code)) {
                $errors++;
                $errorMessages[] = "Le code {$code} doit contenir uniquement des chiffres.";
                continue;
            }

            if (in_array($code, $seenCodes)) {
                $errors++;
                $errorMessages[] = "Le code {$code} est répété dans le fichier CSV.";
                continue;
            }

            $seenCodes[] = $code;

            try {
                Student::updateOrCreate(
                    [
                        'teacher_id' => auth()->id(),
                        'student_code' => $code
                    ],
                    [
                        'first_name' => $firstName,
                        'last_name'  => $lastName,
                        'teacher_id' => auth()->id(),
                    ]
                );
                $importCount++;
            } catch (\Exception $e) {
                $errors++;
            }
        }
    }

    fclose($handle);

    if ($errors > 0) {
        return back()->with(
            'error',
            "$importCount étudiants importés. $errors erreur(s) : " . implode(' | ', array_slice($errorMessages, 0, 5))
        );
    }

    return back()->with('success', "$importCount étudiants importés avec succès !");

}


public function create(Request $request)
{
    $examId = $request->query('exam_id');
    $exam = Exam::find($examId);
    $idLength = $exam ? $exam->student_id_length : 6; 

    return view('students.create', compact('idLength'));
}

public function store(Request $request)
{
    $idLength = $request->input('id_length', 6);

    $validated = $request->validate([
        'first_name'   => 'required|string|max:255',
        'last_name'    => 'required|string|max:255',
        'student_code' => [
            'required',
            'numeric',
            'unique:students,student_code',
        ],
    ], [
        'student_code.unique' => 'Ce code étudiant existe déjà. Veuillez utiliser un code différent.',
        'student_code.required' => 'Le code étudiant est obligatoire.',
        'student_code.numeric' => 'Le code étudiant doit contenir uniquement des chiffres.',
    ]);

    if (str_starts_with(strtoupper($request->student_code), 'TMP')) {
        return back()->withErrors([
            'student_code' => 'Le code TMP est réservé aux copies temporaires.'
        ]);
    }    

    Student::create([
        'first_name'   => $validated['first_name'],
        'last_name'    => $validated['last_name'],
        'student_code' => $validated['student_code'],
        'teacher_id'   => auth()->id(),
    ]);

    return redirect()->route('students.index')->with('success', 'Student added successfully!');
}

    public function destroy(Student $student)
    {
        if ($student->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $student->delete();
        return back()->with('success', 'Student deleted.');
    }

    public function edit(Student $student)
{
    if ($student->teacher_id !== auth()->id()) {
        abort(403, 'Action non autorisée.');
    }

    return view('students.edit', compact('student'));
}

public function update(Request $request, Student $student)
{
    if ($student->teacher_id !== auth()->id()) {
        abort(403);
    }

    $validated = $request->validate([
        'first_name'   => 'required|string|max:255',
        'last_name'    => 'required|string|max:255',
        'student_code' => [
            'required',
            'numeric',
            'unique:students,student_code,' . $student->id,
        ],
    ], [
        'student_code.unique' => 'Ce code étudiant est déjà utilisé par un autre étudiant.',
        'student_code.required' => 'Le code étudiant est obligatoire.',
        'student_code.numeric' => 'Le code étudiant doit contenir uniquement des chiffres.',
    ]);

    $student->update($validated);

    return redirect()->route('students.index')->with('success', 'Étudiant mis à jour avec succès !');
}
}